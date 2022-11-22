<?php declare(strict_types=1);

namespace Torr\Storyblok\Data;

enum AssetFileType : string
{
	case Image = "images";

	case Video = "videos";

	case Audio = "audios";

	case Text = "texts";
}
