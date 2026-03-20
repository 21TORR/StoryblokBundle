<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Webhook\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\Torr\Storyblok\Webhook\Fixtures\TestWebhookAdapter;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Event\StoryblokWebhookEvent;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\StoryFactory;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;
use Torr\Storyblok\Webhook\Controller\WebhookController;
use Torr\Storyblok\Webhook\Parser\WebhookPayloadParser;

/**
 * @internal
 */
final class WebhookControllerTest extends TestCase
{
	/**
	 */
	private function createController () : WebhookController
	{
		$controller = new WebhookController();
		$controller->setContainer(new ServiceLocator([]));

		return $controller;
	}

	/**
	 */
	public function testReturns404ForUnknownAdapter () : void
	{
		$controller = $this->createController();
		$response = $controller->webhook(
			new StoryblokAdapterRegistry(new ServiceLocator([])),
			new NullLogger(),
			new WebhookPayloadParser(new StoryblokAdapterRegistry(new ServiceLocator([])), new NullLogger()),
			self::createStub(EventDispatcherInterface::class),
			Request::create("/", "POST", content: "{}"),
			"missing-adapter",
			null,
		);

		self::assertSame(404, $response->getStatusCode());
		self::assertSame([
			"ok" => false,
			"error" => "unknown_adapter",
		], $this->decodeResponse($response));
	}

	/**
	 */
	public function testReturns403ForInvalidSignature () : void
	{
		[$registry, $parser] = $this->createRegistryAndParser("secret");
		$controller = $this->createController();
		$request = Request::create(
			"/",
			"POST",
			server: [
				"HTTP_WEBHOOK_SIGNATURE" => hash_hmac("sha1", "{\"x\":1}", "wrong-secret"),
			],
			content: "{\"x\":1}",
		);

		$response = $controller->webhook(
			$registry,
			new NullLogger(),
			$parser,
			self::createStub(EventDispatcherInterface::class),
			$request,
			TestWebhookAdapter::getKey(),
			null,
		);

		self::assertSame(403, $response->getStatusCode());
		self::assertSame("invalid / unsigned request", $this->decodeResponse($response)["error"]);
	}

	/**
	 */
	public function testReturns403ForNonPostRequests () : void
	{
		[$registry, $parser] = $this->createRegistryAndParser(null);
		$controller = $this->createController();
		$request = Request::create("/", "GET");

		$response = $controller->webhook(
			$registry,
			new NullLogger(),
			$parser,
			self::createStub(EventDispatcherInterface::class),
			$request,
			TestWebhookAdapter::getKey(),
			null,
		);

		self::assertSame(403, $response->getStatusCode());
	}

	/**
	 */
	public function testReturnsInvalidPayloadWhenParserCannotParse () : void
	{
		[$registry, $parser] = $this->createRegistryAndParser(null);
		$controller = $this->createController();
		$request = Request::create(
			"/",
			"POST",
			content: (string) json_encode([
				"text" => "Story published",
				"action" => "published",
				"space_id" => 12345,
				"story_id" => 1,
			], \JSON_THROW_ON_ERROR),
		);

		$response = $controller->webhook(
			$registry,
			new NullLogger(),
			$parser,
			self::createStub(EventDispatcherInterface::class),
			$request,
			TestWebhookAdapter::getKey(),
			null,
		);

		self::assertSame(200, $response->getStatusCode());
		self::assertSame("invalid payload", $this->decodeResponse($response)["error"]);
	}

	/**
	 */
	public function testDispatchesWebhookEventAndReturnsResponseData () : void
	{
		[$registry, $parser] = $this->createRegistryAndParser(null);
		$dispatcher = $this->createMock(EventDispatcherInterface::class);
		$dispatcher
			->expects(self::once())
			->method("dispatch")
			->willReturnCallback(static function (object $event) : object
			{
				self::assertInstanceOf(StoryblokWebhookEvent::class, $event);
				$event->addResponseData("processed", true);

				return $event;
			});

		$controller = $this->createController();
		$request = Request::create(
			"/",
			"POST",
			content: (string) json_encode([
				"text" => "Story published",
				"action" => "published",
				"space_id" => 12345,
				"story_id" => 1,
				"full_slug" => "en/test",
			], \JSON_THROW_ON_ERROR),
		);

		$response = $controller->webhook(
			$registry,
			new NullLogger(),
			$parser,
			$dispatcher,
			$request,
			TestWebhookAdapter::getKey(),
			null,
		);

		self::assertSame(200, $response->getStatusCode());
		self::assertSame([
			"processed" => true,
			"ok" => true,
		], $this->decodeResponse($response));
	}

	/**
	 */
	public function testReturnsInvalidJsonOnMalformedBody () : void
	{
		[$registry, $parser] = $this->createRegistryAndParser(null);
		$controller = $this->createController();
		$request = Request::create("/", "POST", content: "{invalid");

		$response = $controller->webhook(
			$registry,
			new NullLogger(),
			$parser,
			self::createStub(EventDispatcherInterface::class),
			$request,
			TestWebhookAdapter::getKey(),
			null,
		);

		self::assertSame(200, $response->getStatusCode());
		self::assertSame("invalid JSON", $this->decodeResponse($response)["error"]);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function decodeResponse (JsonResponse $response) : array
	{
		$decoded = json_decode((string) $response->getContent(), true, flags: \JSON_THROW_ON_ERROR);
		self::assertIsArray($decoded);

		$normalized = [];

		foreach ($decoded as $key => $value)
		{
			$normalized[(string) $key] = $value;
		}

		return $normalized;
	}

	/**
	 * @return array{StoryblokAdapterRegistry, WebhookPayloadParser}
	 */
	private function createRegistryAndParser (?string $webhookSecret) : array
	{
		$adapter = $this->createAdapter($webhookSecret);
		$registry = new StoryblokAdapterRegistry(new ServiceLocator([
			TestWebhookAdapter::getKey() => static fn () => $adapter,
		]));

		return [$registry, new WebhookPayloadParser($registry, new NullLogger())];
	}

	/**
	 */
	private function createAdapter (?string $webhookSecret) : TestWebhookAdapter
	{
		$componentManager = new ComponentManager(new ServiceLocator([]));
		$logger = new NullLogger();
		$rateLimiter = self::createStub(LimiterInterface::class);
		$rateLimiter->method("consume")->willReturn(new RateLimit(
			availableTokens: 1,
			retryAfter: new \DateTimeImmutable("-1 second"),
			accepted: true,
			limit: 1,
		));
		$rateLimiterFactory = self::createStub(RateLimiterFactoryInterface::class);
		$rateLimiterFactory->method("create")->willReturn($rateLimiter);

		$context = new ComponentContext(
			$componentManager,
			new DataTransformer(),
			$logger,
			new DataValidator(),
			new ImageDimensionsExtractor(),
		);
		$storyFactory = new StoryFactory($componentManager, $context, $logger);

		$locator = new ServiceLocator([
			HttpClientInterface::class => static fn () => new MockHttpClient(),
			StoryFactory::class => static fn () => $storyFactory,
			ComponentManager::class => static fn () => $componentManager,
			"limiter.storyblok_management" => static fn () => $rateLimiterFactory,
			LoggerInterface::class => static fn () => $logger,
		]);

		return new TestWebhookAdapter(
			$locator,
			new StoryblokConfig(
				spaceId: "12345",
				managementToken: "management-token",
				contentToken: "content-token",
				webhookSecret: $webhookSecret,
			),
		);
	}
}
