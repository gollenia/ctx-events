<?php

declare(strict_types=1);

namespace Contexis\Events\Media\Presentation\Resources;

use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Media\Domain\ImageSize;
use Contexis\Events\Media\Domain\ImageSizes;

final class ImageSizeResource implements \JsonSerializable
{
	public function __construct(
		public string $url,
		public int $width,
		public int $height
	) {
	}

	public static function fromImageSizes(ImageSizes $imageSizes): array
	{
		$result = [];
		foreach ($imageSizes->jsonSerialize() as $key => $size) {
			$result[$key] = self::fromDto($size);
		}
		return $result;
	}

	public static function fromDto(ImageSize $imageSize): self
	{
		return new self(
			url: $imageSize->url,
			width: $imageSize->width,
			height: $imageSize->height
		);
	}

	public function jsonSerialize(): array
	{
		return [
			'url' => $this->url,
			'width' => $this->width,
			'height' => $this->height
		];
	}
}
