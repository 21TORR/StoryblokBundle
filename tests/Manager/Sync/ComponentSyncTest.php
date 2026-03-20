<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Manager\Sync;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\Torr\Storyblok\Webhook\Fixtures\TestWebhookAdapter;
use Torr\Cli\Console\Style\TorrStyle;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Exception\Sync\SyncFailedException;
use Torr\Storyblok\Field\Definition\TextField;
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
#[AllowMockObjectsWithoutExpectations]
final class ComponentSyncTest extends TestCase
{
	/**
	 */
	public function testReturnsTrueIfNoComponentChanged () : void
	{
		$manager = self::createStub(ComponentManager::class);
		$manager->method("getAllUsedComponentsInAdapter")->willReturn([]);

		$io = $this->createIoMock();
		$io->expects(self::once())->method("success");
		$io->expects(self::never())->method("confirm");

		$sync = $this->createComponentSync($manager);
		$adapter = $this->createAdapter([
			new MockResponse((string) json_encode([
				"components" => [],
			], \JSON_THROW_ON_ERROR)),
		]);

		self::assertTrue($sync->syncDefinitionsInteractively($io, $adapter));
	}

	/**
	 */
	public function testReturnsFalseWhenUserAbortsSync () : void
	{
		$manager = self::createStub(ComponentManager::class);
		$manager->method("getAllUsedComponentsInAdapter")->willReturn([
			new ValidSyncComponent(),
		]);

		$io = $this->createIoMock();
		$io->method("confirm")->willReturn(false);
		$io->expects(self::once())->method("caution");

		$sync = $this->createComponentSync($manager);
		$adapter = $this->createAdapter([
			new MockResponse((string) json_encode([
				"components" => [],
			], \JSON_THROW_ON_ERROR)),
		]);

		self::assertFalse($sync->syncDefinitionsInteractively($io, $adapter));
	}

	/**
	 */
	public function testSyncsChangedComponentsWhenForced () : void
	{
		$manager = self::createStub(ComponentManager::class);
		$manager->method("getAllUsedComponentsInAdapter")->willReturn([
			new ValidSyncComponent(),
		]);

		$io = $this->createIoMock();
		$io->expects(self::never())->method("confirm");

		$sync = $this->createComponentSync($manager);
		$adapter = $this->createAdapter([
			new MockResponse((string) json_encode([
				"components" => [],
			], \JSON_THROW_ON_ERROR)),
			new MockResponse((string) json_encode([
				"components" => [],
			], \JSON_THROW_ON_ERROR)),
			new MockResponse((string) json_encode([
				"component" => [
					"name" => ValidSyncComponent::getKey(),
					"id" => 11,
				],
			], \JSON_THROW_ON_ERROR)),
		]);

		self::assertTrue($sync->syncDefinitionsInteractively($io, $adapter, true));
	}

	/**
	 */
	public function testWrapsInvalidConfigurationExceptionAsSyncFailed () : void
	{
		$manager = self::createStub(ComponentManager::class);
		$manager->method("getAllUsedComponentsInAdapter")->willReturn([
			new InvalidSyncComponent(),
		]);

		$sync = $this->createComponentSync($manager);
		$adapter = $this->createAdapter([
			new MockResponse((string) json_encode([
				"components" => [],
			], \JSON_THROW_ON_ERROR)),
		]);

		$this->expectException(SyncFailedException::class);
		$this->expectExceptionMessage("can't have a component without fields");

		$sync->syncDefinitionsInteractively($this->createIoMock(), $adapter, true);
	}

	/**
	 */
	private function createComponentSync (ComponentManager $manager) : ComponentSync
	{
		return new ComponentSync(
			new ComponentNormalizer(new ComponentConfigResolver($manager)),
			new ComponentConfigDiffer(),
			$manager,
		);
	}

	/**
	 */
	private function createIoMock () : TorrStyle&MockObject
	{
		$io = $this->getMockBuilder(TorrStyle::class)
			->disableOriginalConstructor()
			->onlyMethods(["writeln", "success", "confirm", "caution", "write", "newLine"])
			->getMock();
		\assert($io instanceof TorrStyle || $io instanceof MockObject);

		$io->expects(self::any())->method("writeln");
		$io->expects(self::any())->method("write");
		$io->expects(self::any())->method("newLine");

		return $io;
	}

	/**
	 * @param list<MockResponse> $responses
	 */
	private function createAdapter (array $responses) : TestWebhookAdapter
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
			HttpClientInterface::class => static fn () => new MockHttpClient($responses),
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

final class ValidSyncComponent extends AbstractComponent
{
	#[\Override]
	public static function getKey () : string
	{
		return "valid-sync";
	}

	#[\Override]
	protected function configureFields () : array
	{
		return [
			"title" => new TextField("Title"),
		];
	}

	#[\Override]
	protected function getComponentType () : ComponentType
	{
		return ComponentType::Standalone;
	}

	#[\Override]
	public function getDisplayName () : string
	{
		return "Valid Sync";
	}
}

final class InvalidSyncComponent extends AbstractComponent
{
	#[\Override]
	public static function getKey () : string
	{
		return "invalid-sync";
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
		return "Invalid Sync";
	}
}
