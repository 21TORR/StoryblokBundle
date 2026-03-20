<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Command;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\Torr\Storyblok\Webhook\Fixtures\TestWebhookAdapter;
use Torr\Hosting\Hosting\HostingEnvironment;
use Torr\Hosting\Tier\HostingTier;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Command\SyncDefinitionsCommand;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Event\StoryblokDefinitionsSyncedEvent;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Normalizer\ComponentNormalizer;
use Torr\Storyblok\Manager\Sync\ComponentConfigResolver;
use Torr\Storyblok\Manager\Sync\ComponentSync;
use Torr\Storyblok\Manager\Sync\Diff\ComponentConfigDiffer;
use Torr\Storyblok\Story\StoryFactory;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * @internal
 */
final class SyncDefinitionsCommandTest extends TestCase
{
	/**
	 */
	public function testForceSyncIsSkippedInNonProduction () : void
	{
		$componentSync = $this->createComponentSyncWithUsedComponents([]);

		$dispatcher = $this->createMock(EventDispatcherInterface::class);
		$dispatcher->expects(self::never())->method("dispatch");

		$command = new SyncDefinitionsCommand(
			$componentSync,
			new StoryblokAdapterRegistry(new ServiceLocator([])),
			new HostingEnvironment(HostingTier::DEVELOPMENT, false),
			$dispatcher,
		);
		$tester = new CommandTester($command);
		$status = $tester->execute([
			"--force" => true,
		]);

		self::assertSame(Command::SUCCESS, $status);
	}

	/**
	 */
	public function testReturnsFailureWhenSyncThrowsException () : void
	{
		$adapter = $this->createAdapterWithSpaceResponse("Test Space");
		$registry = new StoryblokAdapterRegistry(new ServiceLocator([
			TestWebhookAdapter::getKey() => static fn () => $adapter,
		]));

		$componentSync = $this->createComponentSyncWithUsedComponents([
			new InvalidSyncCommandComponent(),
		]);

		$dispatcher = $this->createMock(EventDispatcherInterface::class);
		$dispatcher->expects(self::never())->method("dispatch");

		$command = new SyncDefinitionsCommand(
			$componentSync,
			$registry,
			new HostingEnvironment(HostingTier::PRODUCTION, false),
			$dispatcher,
		);
		$tester = new CommandTester($command);
		$status = $tester->execute([]);

		self::assertSame(Command::FAILURE, $status);
	}

	/**
	 */
	public function testDispatchesEventOnSuccessfulSync () : void
	{
		$adapter = $this->createAdapterWithSpaceResponse("My Space");
		$registry = new StoryblokAdapterRegistry(new ServiceLocator([
			TestWebhookAdapter::getKey() => static fn () => $adapter,
		]));

		$componentSync = $this->createComponentSyncWithUsedComponents([]);

		$dispatcher = $this->createMock(EventDispatcherInterface::class);
		$dispatcher
			->expects(self::once())
			->method("dispatch")
			->with(self::callback(static fn (object $event) : bool => $event instanceof StoryblokDefinitionsSyncedEvent));

		$command = new SyncDefinitionsCommand(
			$componentSync,
			$registry,
			new HostingEnvironment(HostingTier::PRODUCTION, false),
			$dispatcher,
		);
		$tester = new CommandTester($command);
		$status = $tester->execute([]);

		self::assertSame(Command::SUCCESS, $status);
	}

	/**
	 * @param list<AbstractComponent> $usedComponents
	 */
	private function createComponentSyncWithUsedComponents (array $usedComponents) : ComponentSync
	{
		$manager = self::createStub(ComponentManager::class);
		$manager
			->method("getAllUsedComponentsInAdapter")
			->willReturn($usedComponents);

		return new ComponentSync(
			new ComponentNormalizer(new ComponentConfigResolver($manager)),
			new ComponentConfigDiffer(),
			$manager,
		);
	}

	/**
	 */
	private function createAdapterWithSpaceResponse (string $spaceName) : TestWebhookAdapter
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
			HttpClientInterface::class => static fn () => new MockHttpClient([
				new MockResponse((string) json_encode([
					"space" => [
						"id" => 12345,
						"name" => $spaceName,
						"version" => 1,
						"language_codes" => [],
						"domain" => "example.com",
					],
				], \JSON_THROW_ON_ERROR)),
				new MockResponse((string) json_encode([
					"components" => [],
				], \JSON_THROW_ON_ERROR)),
			]),
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
			),
		);
	}
}

final class InvalidSyncCommandComponent extends AbstractComponent
{
	#[\Override]
	public static function getKey () : string
	{
		return "invalid-sync-command";
	}

	#[\Override]
	protected function configureFields () : array
	{
		return [];
	}

	#[\Override]
	protected function getComponentType () : ComponentType
	{
		return ComponentType::Standalone;
	}

	#[\Override]
	public function getDisplayName () : string
	{
		return "Invalid Sync Command";
	}
}
