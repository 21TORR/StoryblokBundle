<?php declare(strict_types=1);

namespace Torr\Storyblok\RichText;

use Torr\Storyblok\Field\Data\RichTextAssetLinkData;
use Torr\Storyblok\Field\Data\RichTextEmailLinkData;
use Torr\Storyblok\Field\Data\RichTextExternalLinkData;
use Torr\Storyblok\Field\Data\RichTextStoryLinkData;

final class LinkMarksRichTextTransformer extends RichTextTransformer
{
	/**
	 * @inheritDoc
	 */
	protected function transformLinkMark (array $linkMark) : ?array
	{
		$attrs = $this->transformData($linkMark["attrs"]);

		return null !== $attrs
			? [
				...$linkMark,
				"attrs" => $attrs,
			]
			: null;
	}

	/**
	 * @inheritDoc
	 */
	protected function transformData (array $data) : mixed
	{
		$uuid = $this->normalizeOptionalString($data["uuid"]);

		if ("story" === $data["linktype"])
		{
			// we have the cached_url in the data here, but we can't rely on it, as it might be out of date
			return new RichTextStoryLinkData(
				uuid: $uuid,
				anchor: $data["anchor"],
				target: $data["target"],
			);
		}

		if ("email" === $data["linktype"])
		{
			$email = $this->normalizeOptionalString($data["href"]);

			return null !== $email
				? new RichTextEmailLinkData(
					uuid: $uuid,
					email: $email,
					anchor: $data["anchor"],
					target: $data["target"],
				)
				: null;
		}

		if ("asset" === $data["linktype"])
		{
			$url = $this->normalizeOptionalString($data["href"]);

			if (null === $url)
			{
				return null;
			}

			return new RichTextAssetLinkData(
				uuid: $uuid,
				url: $url,
				anchor: $data["anchor"],
				target: $data["target"],
				custom: $data["custom"] ?? null,
			);
		}

		// "url" === $data["linktype"]
		$href = $this->normalizeOptionalString($data["href"]);

		return null !== $href
			? new RichTextExternalLinkData(
				uuid: $uuid,
				href: $href,
				anchor: $data["anchor"],
				target: $data["target"],
			)
			: null;
	}


	/**
	 *
	 */
	private function normalizeOptionalString (?string $value) : ?string
	{
		if (null === $value)
		{
			return null;
		}

		// Normalize to null. Trim for checking, but don't trim data if is not empty, just to be sure.
		return "" !== \trim($value)
			? $value
			: null;
	}
}
