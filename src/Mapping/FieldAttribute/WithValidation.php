<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\FieldAttribute;

use Symfony\Component\Validator\Constraints\NotBlank;


#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class WithValidation extends FieldAttributeInterface
{
	/**
	 */
	public function __construct (
		public bool $required = true,
		public ?string $regexp = null,
		public bool $allowMissingData = false,
	)
	{
		parent::__construct([
			"required" => $this->required,
			"regex" => $this->regexp,
		]);
	}


	/**
	 * @inheritDoc
	 */
	public function getValidationConstraints () : array
	{
		return !$this->required || $this->allowMissingData
			? []
			: [
				// we need to use NotBlank instead of NotNull here, as Storyblok often
				// also returns empty strings for empty fields
				new NotBlank(),
			];
	}
}
