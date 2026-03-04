<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Fixtures;

use Torr\Storyblok\Component\AbstractComponent;
use Torr\Storyblok\Component\Config\ComponentType;

/**
 * @final
 */
class ComponentD extends AbstractComponent
{
	/**
	 */
	public function __construct (
		/** @var list<string> */
		private readonly array $tags = [],
	) {}

	/**
	 *
	 */
	#[\Override]
	public static function getKey () : string
	{
		return "d";
	}

	/**
	 *
	 */
	#[\Override]
	protected function configureFields () : array
	{
		return [];
	}

	/**
	 *
	 */
	#[\Override]
	protected function getComponentType () : ComponentType
	{
		return ComponentType::Standalone;
	}

	/**
	 *
	 */
	#[\Override]
	public function getDisplayName () : string
	{
		return "B";
	}

	/**
	 *
	 */
	#[\Override]
	public function getTags () : array
	{
		return $this->tags;
	}
}
