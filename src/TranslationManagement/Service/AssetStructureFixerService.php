<?php declare(strict_types=1);

namespace Torr\Storyblok\TranslationManagement\Service;

class AssetStructureFixerService
{
	public function fixStoryblokAssetStructure(array $storyJson) : array
	{
		foreach ($storyJson as $key => &$value)
		{
			if (\is_array($value))
			{
				// Check if the current array contains the key "fieldtype" with the value "asset"
				if (\array_key_exists("fieldtype", $value) && "asset" === $value["fieldtype"])
				{
					// If "meta_data" is an empty array set it to empty stdClass
					if (isset($value['meta_data']) && empty($value['meta_data']))
					{
						$value['meta_data'] = new \stdClass();
					}
				}
				// Recursively call processArray on the current element
				$storyJson[$key] = $this->fixStoryblokAssetStructure($value);
			}
		}

		return $storyJson;
	}
}
