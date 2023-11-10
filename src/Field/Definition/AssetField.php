<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Asset\AssetFileType;
use Torr\Storyblok\Field\Data\AssetData;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Visitor\DataVisitorInterface;

/**
 * @phpstan-type RawAssetData array{id: int|null, alt: string|null, name: string, focus: string|null, title: string|null, filename: string|null, copyright: string|null, fieldtype: string, is_external_url: bool}
 */
final class AssetField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function __construct (
		string $label,
		/**
		 * The allowed file types. Empty array allows all file types.
		 *
		 * @var AssetFileType[]
		 */
		private readonly array $fileTypes = [],
		private readonly bool $allowMultiple = false,
		private readonly bool $allowExternalUrl = false,
		mixed $defaultValue = null,
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
			? FieldType::MultiAsset
			: FieldType::Asset;
	}

	/**
	 * @inheritDoc
	 */
	protected function toManagementApiData () : array
	{
		return \array_replace(
			parent::toManagementApiData(),
			[
				"allow_external_url" => $this->allowExternalUrl,
				"filetypes" => \array_map(
					static fn (AssetFileType $fileType) => $fileType->value,
					$this->fileTypes,
				),
			],
		);
	}

	/**
	 * @inheritDoc
	 */
	public function validateData (ComponentContext $context, array $contentPath, mixed $data, array $fullData) : void
	{
		$idConstraints = [
			new Type("int"),
		];
		$fileNameConstraints = [
			new Type("string"),
		];
		$isExternalUrlConstraints = [
			new Type("bool"),
		];

		if (!$this->allowMissingData && $this->required)
		{
			$idConstraints[] = new NotNull();
			$fileNameConstraints[] = new NotNull();
			$isExternalUrlConstraints[] = new NotNull();
		}

		$basicConstraint = [
			new Type("array"),
			// required fields
			new Collection(
				fields: [
					"id" => $idConstraints,
					"filename" => $fileNameConstraints,
					"fieldtype" => [
						new IdenticalTo("asset"),
					],
				],
				allowExtraFields: true,
				allowMissingFields: false,
			),
			// optional fields
			new Collection(
				fields: [
					"alt" => [
						new Type("string"),
					],
					"name" => [
						new Type("string"),
					],
					"focus" => [
						new Type("string"),
					],
					"title" => [
						new Type("string"),
					],
					"source" => [
						new Type("string"),
					],
					"copyright" => [
						new Type("string"),
					],
					"is_external_url" => $isExternalUrlConstraints,
				],
				allowExtraFields: true,
				allowMissingFields: true,
			),
		];

		$constraints = [
			!$this->allowMissingData && $this->required ? new NotNull() : null,
			...$basicConstraint,
		];

		if ($this->allowMultiple)
		{
			$constraints = [
				!$this->allowMissingData && $this->required ? new NotNull() : null,
				new Type("array"),
				new All(
					constraints: $basicConstraint,
				),
			];
		}

		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			$constraints,
		);
	}

	/**
	 * @param RawAssetData|RawAssetData[]|null $data
	 *
	 * @inheritDoc
	 *
	 * @return AssetData|array<AssetData|null>|null
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : AssetData|array|null
	{
		\assert(null === $data || \is_array($data));

		if (null === $data)
		{
			$dataVisitor?->onDataVisit($this, $data);
			return null;
		}

		if ($this->allowMultiple)
		{
			$transformed = [];

			/** @var RawAssetData $assetDataItem */
			foreach ($data as $assetDataItem)
			{
				$transformed[] = $this->transformAssetData($assetDataItem, $context);
			}
		}
		else
		{
			/** @var RawAssetData $data */
			$transformed = $this->transformAssetData($data, $context);
		}

		$dataVisitor?->onDataVisit($this, $transformed);

		return $transformed;
	}

	/**
	 * @param RawAssetData|null $data
	 */
	private function transformAssetData (mixed $data, ComponentContext $context) : ?AssetData
	{
		\assert(null === $data || \is_array($data));

		$transformed = null;

		if (\is_array($data) && null !== $data["filename"])
		{
			$assetUrl = $data["filename"];
			[$width, $height] = $context->extractImageDimensions($assetUrl);

			$transformed = new AssetData(
				url: $assetUrl,
				id: $data["id"],
				alt: $context->normalizeOptionalString($data["alt"] ?? null),
				name: $context->normalizeOptionalString($data["name"] ?? null),
				focus: $context->normalizeOptionalString($data["focus"] ?? null),
				title: $context->normalizeOptionalString($data["title"] ?? null),
				source: $context->normalizeOptionalString($data["source"] ?? null),
				copyright: $context->normalizeOptionalString($data["copyright"] ?? null),
				isExternal: $data["is_external_url"] ?? false,
				width: $width,
				height: $height,
			);
		}

		return $transformed;
	}
}
