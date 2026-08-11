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
use Torr\Storyblok\Story\MetaData\BlockMetaData;
use Torr\Storyblok\Story\MetaData\StoryMetaData;

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
		// every call. As we are producing A TON of calls here,
		// this would otherwise hugely increase the memory consumption.
		$this->validator = Validation::createValidator();
	}

	/**
	 *
	 */
	public function hydrateStandaloneStoryMetaData (
		array $data,
		string $spaceId,
		int $localeLevel,
	) : StoryMetaData
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
									new NotNull(),
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

		return new StoryMetaData(
			uuid: $data["uuid"],
			id: (string) $data["id"],
			type: $data["content"]["component"],
			name: $data["name"],
			fullSlug: $data["full_slug"],
			createdAt: $this->parseDate($data["created_at"]),
			spaceId: $spaceId,
			previewData: $data["content"]["_editable"] ?? null,
			firstPublishedAt: null !== $data["first_published_at"]
				? $this->parseDate($data["first_published_at"])
				: null,
			publishedAt: null !== $data["published_at"]
				? $this->parseDate($data["published_at"])
				: null,
			isStartPage: $data["is_startpage"],
			locale: $data["lang"],
			position: $data["position"] ?? null,
			alternates: $data["alternates"],
			localeLevel: $localeLevel,
		);
	}

	/**
	 */
	public function hydrateNestedStoryMetaData (
		array $data,
		/** @todo actually implement */
		string $spaceId = "0",
	) : BlockMetaData
	{
		$isValid = $this->validator->validate($data, [
			new NotNull(),
			new Collection(
				fields: [
					"_uid" => [
						new NotNull(),
						new Type("string"),
					],
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
						new NotNull(),
						new Type("string"),
					],
				],
				allowExtraFields: true,
				allowMissingFields: true,
			),
		]);

		if (\count($isValid) > 0)
		{
			throw new BrokenStoryDataException(\sprintf(
				"Invalid document meta data: %s",
				$isValid,
			));
		}

		return new BlockMetaData(
			uuid: $data["_uid"],
			type: $data["component"],
			spaceId: $spaceId,
			previewData: $data["_editable"] ?? null,
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
