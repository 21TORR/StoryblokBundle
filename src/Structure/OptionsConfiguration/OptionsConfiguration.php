<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\OptionsConfiguration;

interface OptionsConfiguration
{
	public function getSerializedConfig () : array;
}
