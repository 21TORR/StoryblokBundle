<?php declare(strict_types=1);

namespace Torr\Storyblok\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Torr\Storyblok\Exception\Definition\DuplicateComponentKeyException;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Mapping\Storyblok;
use Torr\Storyblok\Reflection\ReflectionHelper;

/**
 * Compiler pass that iterates through all services, collects all storyblok types
 * and removes them from the container.
 */
final class CollectComponentDefinitionsCompilerPass implements CompilerPassInterface
{
	/**
	 */
	public function __construct (
		private readonly ReflectionHelper $reflectionHelper = new ReflectionHelper(),
	) {}

	/**
	 * @inheritDoc
	 */
	public function process (ContainerBuilder $container) : void
	{
		$definitions = [];

		foreach ($container->getDefinitions() as $id => $definition)
		{
			/** @var class-string|null $class */
			$class = $definition->getClass();

			if (null === $class || !\str_contains($class, "Storyblok"))
			{
				continue;
			}

			$attribute = $this->reflectionHelper->generateAttribute(Storyblok::class, $class);

			if (null === $attribute)
			{
				continue;
			}

			// @todo verify type (base class) of story

			if (\array_key_exists($attribute->key, $definitions))
			{
				throw new DuplicateComponentKeyException(\sprintf(
					"Found multiple storyblok definitions for key '%s'. One in '%s' and one in '%s'",
					$attribute->key,
					$definitions[$attribute->key],
					$definition->getClass(),
				));
			}

			$definitions[$attribute->key] = $class;
			$container->removeDefinition($id);
		}

		$container->getDefinition(ComponentManager::class)
			->setArgument('$classesWithDefinitions', $definitions);
	}
}
