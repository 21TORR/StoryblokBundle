<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Validator;

final readonly class ComponentDataValidator
{
	public static function isComponent (array $data) : bool
	{
		return \array_key_exists('component', $data) && \array_key_exists('_uid', $data);
	}
}
