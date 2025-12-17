<?php declare(strict_types=1);

namespace Torr\Storyblok\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Validator\Constraints as Assert;
use Torr\Rad\Entity\EntityInterface;
use Torr\Rad\Entity\ModifiableEntityFieldsTrait;
use Torr\Storyblok\Exception\Asset\InvalidAssetDataUrlException;

use function Symfony\Component\Clock\now;

/**
 * @final
 */
#[Entity]
#[Table(name: "storyblok_asset_data")]
class AssetDataEntity implements EntityInterface
{
	use ModifiableEntityFieldsTrait;

	#[Assert\NotBlank]
	#[Assert\Length(max: 1000)]
	#[ORM\Column(type: Types::STRING, length: 1000)]
	public readonly string $externalId;

	#[Assert\NotBlank]
	#[Assert\Length(max: 1000)]
	#[ORM\Column(type: Types::STRING, length: 1000)]
	public ?string $url = null;

	/** @var string[] */
	#[ORM\Column]
	public array $tags = [];

	#[ORM\Column]
	public bool $private = false;

	#[ORM\Column]
	public bool $deleted = false;

	/**
	 */
	public function __construct (string $externalId)
	{
		$this->externalId = $externalId;
		$this->timeCreated = now();
	}

	/**
	 */
	public function getAssetPath () : string
	{
		if (!$this->url || !preg_match('~^https://s3.amazonaws.com/a.storyblok.com/f/\d+/(?P<path>.+)$~D', $this->url, $matches))
		{
			throw new InvalidAssetDataUrlException(\sprintf("Invalid asset data URL '%s'.", $this->url));
		}

		return $matches["path"];
	}
}
