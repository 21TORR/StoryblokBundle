<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Normalizer;

use Torr\Storyblok\TranslationManagement\Data\TranslatableContentCollection;
use Torr\Storyblok\TranslationManagement\Exception\TranslationManagementExceptionInterface;

interface NormalizerInterface
{
	/**
	 * @throws TranslationManagementExceptionInterface
	 */
	public function normalize (TranslatableContentCollection $data) : string;

	/**
	 * @throws TranslationManagementExceptionInterface
	 */
	public function denormalize (string $data) : TranslatableContentCollection;
}
