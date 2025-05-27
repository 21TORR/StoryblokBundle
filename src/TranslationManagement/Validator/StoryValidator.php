<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Validator;

final readonly class StoryValidator
{
	public static function isValid (array $data) : bool
	{
		return
			\array_key_exists('id', $data)
			&& \array_key_exists('slug', $data)
			&& \array_key_exists('full_slug', $data)
			&& \array_key_exists('name', $data);
	}
}
