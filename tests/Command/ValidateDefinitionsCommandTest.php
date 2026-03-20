<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Command;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Tests\Torr\Storyblok\Webhook\Fixtures\TestWebhookAdapter;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Command\ValidateDefinitionsCommand;
use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Config\ComponentType;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Image\ImageDimensionsExtractor;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Manager\Normalizer\ComponentNormalizer;
use Torr\Storyblok\Manager\Sync\ComponentConfigResolver;
use Torr\Storyblok\Manager\Validator\ComponentValidator;
use Torr\Storyblok\Story\StoryFactory;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Validator\DataValidator;

/**
 * @internal
 */
final class ValidateDefinitionsCommandTest extends TestCase
{
	/**
	 */
	public function testReturnsSuccessWhenAllAdaptersValidate () : void
	{
		$adapter = $this->createAdapterWithSpaceResponse("Validated Space");
		$registry = new StoryblokAdapterRegistry(new ServiceLocator([
			TestWebhookAdapter::getKey() => static fn () => $adapter,
		]));

		$validator = $this->createValidatorWithUsedComponents([]);

		$command = new ValidateDefinitionsCommand($validator, $registry);
		$tester = new CommandTester($command);
		$status = $tester->execute([]);

		self::assertSame(Command::SUCCESS, $status);
	}

	/**
	 */
	public function testReturnsFailureWhenValidationFails () : void
	{
		$adapter = $this->createAdapterWithSpaceResponse("Broken Space");
		$registry = new StoryblokAdapterRegistry(new ServiceLocator([
			TestWebhookAdapter::getKey() => static fn () => $adapter,
		]));

		$validator = $this->createValidatorWithUsedComponents([
			new InvalidValidateComponent(),
		]);

		$command = new ValidateDefinitionsCommand($validator, $registry);
		$tester = new CommandTester($command);
		$status = $tester->execute([]);

		self::assertSame(Command::FAILURE, $status);
	}

	/**
	 * @param list<AbstractComponent> $usedComponents
	 */
	private function createValidatorWithUsedComponents (array $usedComponents) : ComponentValidator
	{
		$manager = $this->createStub(ComponentManager::class);
		$manager
			->method("getAllUsedComponentsInAdapter")
			->willReturn($usedComponents);

		return new ComponentValidator(
			new ComponentNormalizer(new ComponentConfigResolver($manager)),
			$manager,
		);
	}

	/**
	 */
	private function createAdapterWithSpaceResponse (string $spaceName) : TestWebhookAdapter
	{
		$componentManager = new ComponentManager(new ServiceLocator([]));
		$logger = new NullLogger();
		$rateLimiter = $this->createStub(LimiterInterface::class);
		$rateLimiter->method("consume")->willReturn(new RateLimit(
			availableTokens: 1,
			retryAfter: new \DateTimeImmutable("-1 second"),
			accepted: true,
			limit: 1,
		));
		$rateLimiterFactory = $this->createStub(RateLimiterFactoryInterface::class);
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
			\Symfony\Contracts\HttpClient\HttpClientInterface::class => static fn () => new MockHttpClient([
				new MockResponse((string) json_encode([
					"space" => [
						"id" => 12345,
						"name" => $spaceName,
						"version" => 1,
						"language_codes" => [],
						"domain" => "example.com",
					],
				], \JSON_THROW_ON_ERROR)),
			]),
			StoryFactory::class => static fn () => $storyFactory,
			ComponentManager::class => static fn () => $componentManager,
			"limiter.storyblok_management" => static fn () => $rateLimiterFactory,
			\Psr\Log\LoggerInterface::class => static fn () => $logger,
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

final class InvalidValidateComponent extends AbstractComponent
{
	#[\Override]
	public static function getKey () : string
	{
		return "invalid-validate";
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
		return "Invalid Validate";
	}
}
