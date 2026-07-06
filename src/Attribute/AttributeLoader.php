<?php declare(strict_types=1);

namespace Torr\Storyblok\Attribute;

use Torr\Storyblok\Definition\Mapping\Blok;
use Torr\Storyblok\Definition\Mapping\Document;

/**
 * @final
 */
readonly class AttributeLoader
{
	/**
	 * @template AttributeType of object
	 * @param class-string $storyClass
	 * @param class-string<AttributeType> $attribute
	 *
	 * @return AttributeType|null
	 */
	public function loadAttribute (string $storyClass, string $attribute) : ?object
	{
		$reflection = new \ReflectionClass($storyClass);
		return $reflection->getAttributes($attribute)[0]?->newInstance() ?? null;
	}


	/**
	 * @param class-string $storyClass
	 */
	public function loadBlok (string $storyClass) : ?Blok
	{
		return $this->loadAttribute($storyClass, Blok::class);
	}


	/**
	 * @param class-string $storyClass
	 */
	public function loadDocument (string $storyClass) : ?Document
	{
		return $this->loadAttribute($storyClass, Document::class);
	}
}
