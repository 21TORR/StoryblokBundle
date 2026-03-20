<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Webhook\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\Torr\Storyblok\Webhook\Fixtures\TestWebhookAdapter;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Story\StoryFactory;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;
use Torr\Storyblok\Webhook\Action\WebhookAction;
use Torr\Storyblok\Webhook\Parser\WebhookPayloadParser;
use Torr\Storyblok\Webhook\Payload\AssetWebhookPayload;
use Torr\Storyblok\Webhook\Payload\DatasourceEntryWebhookPayload;
use Torr\Storyblok\Webhook\Payload\PipelineWebhookPayload;
use Torr\Storyblok\Webhook\Payload\ReleaseWebhookPayload;
use Torr\Storyblok\Webhook\Payload\StoryWebhookPayload;
use Torr\Storyblok\Webhook\Payload\UserWebhookPayload;
use Torr\Storyblok\Webhook\Payload\WorkflowStageWebhookPayload;

/**
 * @internal
 */
final class WebhookPayloadParserTest extends TestCase
{
	/**
	 */
	public function testParseAssetWebhookPayload () : void
	{
		$parser = $this->createParser();

		$payload = $parser->parseFromRawArray([
			"text" => "Asset created\nhttps://a.storyblok.com/f/123/uploads/image.jpg",
			"action" => "created",
			"space_id" => 12345,
			"asset_id" => 77,
		], "12345");

		self::assertInstanceOf(AssetWebhookPayload::class, $payload);
		self::assertSame(WebhookAction::AssetCreated, $payload->action);
		self::assertSame(77, $payload->assetId);
		self::assertSame("/uploads/image.jpg", $payload->assetPath);
	}

	/**
	 */
	#[DataProvider("provideParseTypedWebhookPayloads")]
	public function testParseTypedWebhookPayloads (
		array $payload,
		string $expectedClass,
		WebhookAction $expectedAction,
	) : void
	{
		$parser = $this->createParser();
		$parsed = $parser->parseFromRawArray($payload, "12345");

		self::assertInstanceOf($expectedClass, $parsed);
		self::assertSame($expectedAction, $parsed->action);
	}

	/**
	 */
	public static function provideParseTypedWebhookPayloads () : iterable
	{
		yield "datasource" => [
			[
				"text" => "Datasource updated",
				"action" => "entries_updated",
				"space_id" => 12345,
				"datasource_slug" => "colors",
			],
			DatasourceEntryWebhookPayload::class,
			WebhookAction::DatasourceEntryUpdated,
		];

		yield "story" => [
			[
				"text" => "Story published",
				"action" => "published",
				"space_id" => 12345,
				"story_id" => 42,
				"full_slug" => "en/story",
			],
			StoryWebhookPayload::class,
			WebhookAction::StoryPublished,
		];

		yield "pipeline" => [
			[
				"text" => "Pipeline deployed",
				"action" => "deployed",
				"space_id" => 12345,
				"branch_id" => 13,
			],
			PipelineWebhookPayload::class,
			WebhookAction::PipelineDeployed,
		];

		yield "user" => [
			[
				"text" => "User added",
				"action" => "added",
				"space_id" => 12345,
				"user_id" => 7,
			],
			UserWebhookPayload::class,
			WebhookAction::UserAdded,
		];

		yield "release" => [
			[
				"text" => "Release merged",
				"action" => "merged",
				"space_id" => 12345,
				"release_id" => 9,
			],
			ReleaseWebhookPayload::class,
			WebhookAction::ReleaseMerged,
		];

		yield "workflow" => [
			[
				"text" => "Workflow stage changed",
				"action" => "stage.changed",
				"space_id" => 12345,
				"workflow_name" => "Editorial",
				"workflow_stage_name" => "Review",
				"story_id" => 42,
			],
			WorkflowStageWebhookPayload::class,
			WebhookAction::WorkflowStageChanged,
		];
	}

	/**
	 */
	public function testParseReturnsNullOnInvalidBasicStructure () : void
	{
		$parser = $this->createParser();

		self::assertNull($parser->parseFromRawArray([
			"text" => "x",
			"action" => "published",
			"space_id" => "12345",
		], "12345"));
	}

	/**
	 */
	public function testParseReturnsNullOnSpaceMismatch () : void
	{
		$parser = $this->createParser();

		self::assertNull($parser->parseFromRawArray([
			"text" => "Story published",
			"action" => "published",
			"space_id" => 99999,
			"story_id" => 5,
			"full_slug" => "en/story",
		], "12345"));
	}

	/**
	 */
	public function testParseReturnsNullWithoutMatchingAdapter () : void
	{
		$parser = $this->createParser(withAdapter: false);

		self::assertNull($parser->parseFromRawArray([
			"text" => "Story published",
			"action" => "published",
			"space_id" => 12345,
			"story_id" => 5,
			"full_slug" => "en/story",
		], "12345"));
	}

	/**
	 */
	public function testParseReturnsNullOnInvalidActionForType () : void
	{
		$parser = $this->createParser();

		self::assertNull($parser->parseFromRawArray([
			"text" => "Workflow stage changed",
			"action" => "invalid",
			"space_id" => 12345,
			"workflow_name" => "Editorial",
			"workflow_stage_name" => "Review",
			"story_id" => 42,
		], "12345"));
	}

	/**
	 */
	public function testParseReturnsNullIfNoActionMatcherFits () : void
	{
		$parser = $this->createParser();

		self::assertNull($parser->parseFromRawArray([
			"text" => "Unknown event",
			"action" => "published",
			"space_id" => 12345,
		], "12345"));
	}

	/**
	 */
	private function createParser (bool $withAdapter = true) : WebhookPayloadParser
	{
		$registry = new StoryblokAdapterRegistry(new ServiceLocator(
			$withAdapter
				? [TestWebhookAdapter::getKey() => fn () => $this->createAdapter()]
				: [],
		));

		return new WebhookPayloadParser($registry, new NullLogger());
	}

	/**
	 */
	private function createAdapter () : TestWebhookAdapter
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
				webhookSecret: null,
			),
		);
	}
}
