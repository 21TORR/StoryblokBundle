<?php declare(strict_types=1);

namespace Torr\Storyblok\Transformer;

final class DataTransformer
{
	/**
	 * @template T
	 *
	 * @param T $data The transformed data of the component
	 *
	 * @returns T|null
	 */
	public function transformValue (
		mixed $data,
		mixed $component,
	) : mixed
	{
		// @todo add real implementation
		return $data;
	}

	/**
	 *
	 */
	public function normalizeOptionalString (?string $value) : ?string
	{
		if (null === $value)
		{
			return null;
		}

		return "" !== \trim($value)
			? $value
			: null;
	}
}
