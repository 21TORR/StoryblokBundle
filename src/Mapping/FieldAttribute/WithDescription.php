<?php declare(strict_types=1);

namespace Torr\Storyblok\Mapping\FieldAttribute;


#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class WithDescription extends FieldAttributeInterface
{
	public function __construct (
		public string $description,
		public bool $showAsTooltip = false,
	)
	{
		parent::__construct([
			"description" => $this->description,
			"tooltip" => $this->showAsTooltip,
		]);
	}
}
