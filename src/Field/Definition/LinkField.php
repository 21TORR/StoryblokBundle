<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Component\Filter\ComponentFilter;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Data\AssetLinkData;
use Torr\Storyblok\Field\Data\EmailLinkData;
use Torr\Storyblok\Field\Data\ExternalLinkData;
use Torr\Storyblok\Field\Data\StoryLinkData;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Manager\Sync\Filter\ResolvableComponentFilter;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Visitor\DataVisitorInterface;

final class LinkField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		mixed $defaultValue = null,
		private readonly bool $allowEmailLinks = true,
		private readonly bool $allowAssetLinks = false,
		private readonly bool $allowAnchors = true,
		private readonly ?string $internalLinkScope = null,
		private readonly ComponentFilter $allowedComponents = new ComponentFilter(),
	)
	{
		parent::__construct($label, $defaultValue);
	}

	/**
	 * @inheritDoc
	 */
	protected function getInternalStoryblokType () : FieldType
	{
		return FieldType::MultiLink;
	}

	/**
	 * @inheritDoc
	 */
	protected function toManagementApiData () : array
	{
		return \array_replace(
			parent::toManagementApiData(),
			[
				"asset_link_type" => $this->allowAssetLinks,
				"email_link_type" => $this->allowEmailLinks,
				"show_anchor" => $this->allowAnchors,
				"force_link_scope" => !empty($this->internalLinkScope),
				"link_scope" => $this->internalLinkScope,
				"component_whitelist" => new ResolvableComponentFilter(
					$this->allowedComponents,
					"component_whitelist",
					"restrict_content_types",
				),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				!$this->allowMissingData && $this->required ? new NotNull() : null,
				new Type("array"),
				new Collection(
					fields: [
						"id" => [
							new NotNull(),
							new Type("string"),
						],
						"url" => [
							new NotNull(),
							new Type("string"),
						],
						"linktype" => [
							new NotNull(),
							new Type("string"),
							new Choice([
								"asset",
								"email",
								"story",
								"url",
							]),
						],
						"fieldtype" => [
							new NotNull(),
							new IdenticalTo($this->getInternalStoryblokType()->value),
						],
					],
					allowExtraFields: true,
					allowMissingFields: false,
				),
				new Collection(
					fields: [
						"anchor" => [
							new NotNull(),
							new Type("string"),
						],
					],
					allowExtraFields: true,
					allowMissingFields: true,
				),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : StoryLinkData|EmailLinkData|ExternalLinkData|AssetLinkData|null
	{
		\assert(null === $data || \is_array($data));

		$transformed = null !== $data
			? $this->transformDataToLink($context, $data)
			: null;

		$dataVisitor?->onDataVisit($this, $transformed);
		return $transformed;
	}


	/**
	 * Transforms the data array to a link
	 */
	private function transformDataToLink (
		ComponentContext $context,
		array $data,
	) : StoryLinkData|EmailLinkData|ExternalLinkData|AssetLinkData|null
	{
		if ("story" === $data["linktype"])
		{
			$id = DataTransformer::normalizeOptionalString($data["id"]);

			// we have the cached_url in the data here, but we can't rely on it, as it might be out of date
			return new StoryLinkData(
				id: $id,
				anchor: DataTransformer::normalizeOptionalString($data["anchor"] ?? null),
			);
		}

		if ("email" === $data["linktype"])
		{
			$email = DataTransformer::normalizeOptionalString($data["email"]);

			return null !== $email
				? new EmailLinkData($email)
				: null;
		}

		if ("asset" === $data["linktype"])
		{
			$url = DataTransformer::normalizeOptionalString($data["url"]);

			if (null === $url)
			{
				return null;
			}

			[$width, $height] = $context->extractImageDimensions($url);
			return new AssetLinkData($url, $width, $height);
		}

		// "url" === $data["linktype"]
		$url = DataTransformer::normalizeOptionalString($data["url"]);

		return null !== $url
			? new ExternalLinkData($url)
			: null;
	}

}
