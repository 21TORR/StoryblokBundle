<?php declare(strict_types=1);


use PhpParser\Node\Stmt\Block;
use Torr\Storyblok\Definition\Mapping as Storyblok;


#[Storyblok\Blok(
	title: "Text Block",
	key: "text-block",
)]
#[Storyblok\EditorTab(
	"Content",
	["headline", "content"],
)]
#[Storyblok\EditorTab(
	"Settings",
	["nested"],
	\Torr\Storyblok\Translation\LocaleHelper::isValidLocale("test"),
)]
class TextBlock
{
	#[Storyblok\TextField("Headline")]
	public string $headline;

	#[Storyblok\ChoiceField(
		"Kategorien",
		choices: new \Torr\Storyblok\Field\Choices\StoryChoices(),
	)]
	public string $headline;

	#[Storyblok\TextField("Label")]
	public string $label;

	#[Storyblok\RichTextField("Content")]
	public array $content;

	#[Storyblok\Nested(
		"Link",
		prefix: "nested_",
		collapsible: true,
	)]
	public NestedType $nested;


	/**
	 * @var TextBlock[]
	 */
	#[Storyblok\BlocksField()]
	public array $blocks;
}

class NestedType
{
	#[Storyblok\TextField("Label")]
	public string $label;

	#[Storyblok\LinkField("Link")]
	public string $url;
}

$textBlock = new TextBlock();
