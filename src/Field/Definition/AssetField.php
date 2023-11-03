<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\ComponentContext;
use Torr\Storyblok\Field\Asset\AssetFileType;
use Torr\Storyblok\Field\Data\AssetData;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Transformer\DataTransformer;
use Torr\Storyblok\Visitor\DataVisitorInterface;

/**
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

		$context->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				!$this->allowMissingData && $this->required ? new NotNull() : null,
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
			],
		);
	}

	/**
	 * @param array{id: int|null, alt: string|null, name: string, focus: string|null, title: string|null, filename: string|null, copyright: string|null, fieldtype: string, is_external_url: bool}|null $data
	 *
	 * @inheritDoc
	 */
	public function transformData (
		mixed $data,
		ComponentContext $context,
		array $fullData,
		?DataVisitorInterface $dataVisitor = null,
	) : ?AssetData
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
				alt: DataTransformer::normalizeOptionalString($data["alt"] ?? null),
				name: DataTransformer::normalizeOptionalString($data["name"] ?? null),
				focus: DataTransformer::normalizeOptionalString($data["focus"] ?? null),
				title: DataTransformer::normalizeOptionalString($data["title"] ?? null),
				source: DataTransformer::normalizeOptionalString($data["source"] ?? null),
				copyright: DataTransformer::normalizeOptionalString($data["copyright"] ?? null),
				isExternal: $data["is_external_url"] ?? false,
				width: $width,
				height: $height,
			);
		}

		$dataVisitor?->onDataVisit($this, $transformed);
		return $transformed;
	}
}
