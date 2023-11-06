<?php declare(strict_types=1);

namespace Torr\Storyblok\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Torr\Storyblok\Exception\Component\DuplicateComponentKeyException;
use Torr\Storyblok\Exception\Component\InvalidComponentDefinitionException;
use Torr\Storyblok\Manager\ComponentManager;
use Torr\Storyblok\Mapping\StoryBlok;
use Torr\Storyblok\Mapping\StoryDocument;
use Torr\Storyblok\Story\Document;

final class CollectComponentDefinitionsCompilerPass implements CompilerPassInterface
{
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

			$attribute = $this->extractKey($class);

			if (null === $attribute)
			{
				continue;
			}

			if (!\is_a($class, $attribute->getRequiredExtendedClass(), true))
			{
				throw new InvalidComponentDefinitionException(\sprintf(
					"Storyblok element '%s' must extend %s.",
					$class,
					$attribute->getRequiredExtendedClass(),
				));
			}

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
			->setArgument('$storyComponents', $definitions);
	}


	/**
	 * Extracts the key of the class
	 *
	 * @param class-string $class
	 */
	private function extractKey (string $class) : StoryDocument|StoryBlok|null
	{
		try
		{
			$reflectionClass = new \ReflectionClass($class);
			$reflectionAttribute = $reflectionClass->getAttributes(StoryDocument::class)[0]
				?? $reflectionClass->getAttributes(StoryBlok::class)[0]
				?? null;

			if (null === $reflectionAttribute)
			{
				return null;
			}

			$attribute = $reflectionAttribute->newInstance();
			\assert($attribute instanceof StoryDocument || $attribute instanceof StoryBlok);

			return $attribute;
		}
		catch (\ReflectionException)
		{
			return null;
		}
	}
}
