<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Definition;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Torr\Storyblok\Context\StoryblokContext;
use Torr\Storyblok\Field\Asset\AssetFileType;
use Torr\Storyblok\Field\Data\AssetData;
use Torr\Storyblok\Field\FieldType;
use Torr\Storyblok\Validator\DataValidator;
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
	public function toManagementApiData (int $position) : array
	{
		return \array_replace(
			parent::toManagementApiData($position),
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
	public function validateData (DataValidator $validator, array $contentPath, mixed $data) : void
	{
		$validator->ensureDataIsValid(
			$contentPath,
			$this,
			$data,
			[
				new Type("array"),
				// required fields
				new Collection(
					fields: [
						"id" => [
							new Type("int"),
						],
						"filename" => [
							new Type("string"),
						],
						"fieldtype" => [
							new IdenticalTo("asset"),
						],
						"is_external_url" => [
							new NotNull(),
							new Type("bool"),
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
		StoryblokContext $dataContext,
		?DataVisitorInterface $dataVisitor = null,
	) : ?AssetData
	{
		\assert(null === $data || \is_array($data));

		$transformed = null;

		if (\is_array($data) && null !== $data["filename"])
		{
			$dataTransformer = $dataContext->dataTransformer;

			$transformed = new AssetData(
				url: $data["filename"],
				id: $data["id"],
				alt: $dataTransformer->normalizeOptionalString($data["alt"] ?? null),
				name: $dataTransformer->normalizeOptionalString($data["name"] ?? null),
				focus: $dataTransformer->normalizeOptionalString($data["focus"] ?? null),
				title: $dataTransformer->normalizeOptionalString($data["title"] ?? null),
				source: $dataTransformer->normalizeOptionalString($data["source"] ?? null),
				copyright: $dataTransformer->normalizeOptionalString($data["copyright"] ?? null),
				isExternal: $data["is_external_url"],
			);
		}

		return parent::transformData($transformed, $dataContext, $dataVisitor);
	}
}
