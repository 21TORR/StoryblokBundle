<?php declare(strict_types=1);

namespace Torr\Storyblok\Field\Option;

interface ChoiceSourceInterface
{
	/**
	 * Returns the data for the management API
	 */
	public function toManagementApiData () : array;
}
