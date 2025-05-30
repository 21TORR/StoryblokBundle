<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Normalizer;

use Torr\Storyblok\TranslationManagement\Data\TranslationDataCollection;
use Torr\Storyblok\TranslationManagement\Exception\TranslationManagementExceptionInterface;

interface NormalizerInterface
{
	/**
	 * @throws TranslationManagementExceptionInterface
	 */
	public function normalize (TranslationDataCollection $data) : string;
}
