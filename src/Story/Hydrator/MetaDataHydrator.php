<?php declare(strict_types=1);

namespace Torr\Storyblok\Story\Hydrator;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Torr\Storyblok\Exception\Story\StoryHydrationFailed;
use Torr\Storyblok\Story\Exception\BrokenStoryDataException;
use Torr\Storyblok\Story\MetaData\DocumentMetaData;

/**
 * @final
 */
readonly class MetaDataHydrator
{
	private ValidatorInterface $validator;

	/**
	 */
	public function __construct ()
	{
		// We don't use the validator from the DI container here,
		// as in debug it will be a traceable validator that logs
		// every call. As we are producing A TON of calls here
		// this will hugely increase the memory consumption.
		$this->validator = Validation::createValidator();
	}

	/**
	 *
	 */
	public function hydrateDocumentMetaData (
		array $data,
		string $spaceId,
		int $localeLevel,
	) : DocumentMetaData
	{
		$isValid = $this->validator->validate($data, [
			new NotNull(),
			new Collection(
				fields: [
					"content" => [
						new NotNull(),
						new Type("array"),
						new Collection(
							fields: [
								"component" => [
									new NotNull(),
									new Type("string"),
								],
							],
							allowExtraFields: true,
							allowMissingFields: false,
						),
						new Collection(
							fields: [
								"_editable" => [
									new Type("string"),
								],
							],
							allowExtraFields: true,
							allowMissingFields: true,
						),
					],
					"name" => [
						new NotNull(),
						new Type("string"),
					],
					"full_slug" => [
						new NotNull(),
						new Type("string"),
					],
					"created_at" => [
						new NotNull(),
						new Type("string"),
					],
					"first_published_at" => [
						new Type("string"),
					],
					"published_at" => [
						new Type("string"),
					],
					"id" => [
						new NotNull(),
						new Type("int"),
					],
					"uuid" => [
						new NotNull(),
						new Type("string"),
					],
					"is_startpage" => [
						new NotNull(),
						new Type("bool"),
					],
					"lang" => [
						new NotNull(),
						new Type("string"),
					],
					"position" => [
						new Type("int"),
					],
					"alternates" => [
						new NotNull(),
						new Type("array"),
						new All([
							new NotNull(),
							new Collection(
								fields: [
									"name" => [
										new NotNull(),
										new Type("string"),
									],
									"slug" => [
										new NotNull(),
										new Type("string"),
									],
									"published" => [
										new NotNull(),
										new Type("bool"),
									],
									"full_slug" => [
										new NotNull(),
										new Type("string"),
									],
									"is_folder" => [
										new NotNull(),
										new Type("bool"),
									],
									"parent_id" => [
										new NotNull(),
										new Type("int"),
									],
								],
								allowExtraFields: true,
								allowMissingFields: false,
							),
						]),
					],
				],
				allowExtraFields: true,
				allowMissingFields: false,
			),
		]);

		if (\count($isValid) > 0)
		{
			throw new BrokenStoryDataException(\sprintf(
				"Invalid document meta data: %s",
				$isValid,
			));
		}

		return new DocumentMetaData(
			uuid: $data["uuid"],
			type: $data["content"]["component"],
			previewData: $data["content"]["_editable"] ?? null,
			name: $data["name"],
			fullSlug: $data["full_slug"],
			createdAt: $this->parseDate($data["created_at"]),
			firstPublishedAt: null !== $data["first_published_at"]
				? $this->parseDate($data["first_published_at"])
				: null,
			publishedAt: null !== $data["published_at"]
				? $this->parseDate($data["published_at"])
				: null,
			id: (string) $data["id"],
			isStartPage: $data["is_startpage"],
			locale: $data["lang"],
			position: $data["position"] ?? null,
			alternates: $data["alternates"],
			spaceId: $spaceId,
			localeLevel: $localeLevel,
		);
	}

	/**
	 */
	private function parseDate (string $date) : \DateTimeImmutable
	{
		$parsed = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $date);

		if (false === $parsed)
		{
			throw new StoryHydrationFailed(\sprintf(
				"Could not parse date: %s",
				$date,
			));
		}

		return $parsed;
	}
}
