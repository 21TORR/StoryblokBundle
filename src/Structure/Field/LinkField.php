<?php declare(strict_types=1);

namespace Torr\Storyblok\Structure\Field;

use Torr\Storyblok\Data\FieldType;
use Torr\Storyblok\Data\LinkType;
use Torr\Storyblok\Structure\Component\ComponentTypeDefinition;
use Torr\Storyblok\Validator\DataValidator;

final class LinkField extends AbstractField
{
	/**
	 * @inheritDoc
	 *
	 * @param array<class-string<ComponentTypeDefinition>>|null $restrictToContentTypes,
	 */
	public function __construct (
		string $label,
		mixed $defaultValue = null,
		private readonly bool $allowEmailLinks = true,
		private readonly bool $allowAssetLinks = false,
		private readonly bool $allowAnchors = true,
		private readonly bool $restrictInternalLinksToSameFolder = false,
		private readonly bool $allowMultiple = false,
		private readonly ?string $internalLinkScope = null,
		private readonly ?array $restrictToContentTypes = null,
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return $this->allowMultiple
			? FieldType::MultiLink
			: FieldType::Link;
	}

	/**
	 * @inheritDoc
	 */
	public function toManagementApiData (int $position) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
			[
				"asset_link_type" => $this->allowAssetLinks,
				"email_link_type" => $this->allowEmailLinks,
				"show_anchor" => $this->allowAnchors,
				"force_link_scope" => $this->restrictInternalLinksToSameFolder,
				"link_scope" => $this->internalLinkScope,
				"component_whitelist" => $this->restrictToContentTypes,
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (DataValidator $validator, array $path, mixed $data) : void
	{
		// @todo add implementation
	}
}
