<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Presentation\Resources;

use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Media\Domain\ImageSizes;
use Contexis\Events\Shared\Presentation\Contracts\Resource;

class ImageResource implements Resource
{
    public function __construct(
        public string $url,
		public string $altText,
		public int $width,
		public int $height,
		public string $mimeType,
		/** @var array<string, ImageSizeResource> $sizes */
		public array $sizes
    ) {
    }

	public static function fromDto(ImageDto $imageDto, bool $includeSchema = false): self
	{
		return new self(
			url: $imageDto->url,
			altText: $imageDto->altText,
			width: $imageDto->width,
			height: $imageDto->height,
			mimeType: $imageDto->mimeType,
			sizes: $imageDto->sizes !== null
				? ImageSizeResource::fromImageSizes($imageDto->sizes)
				: []
		);
	}


    private function getJsonLd(): array
    {
        $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "ImageObject"
        ];

        return $jsonLd;
    }

    public function jsonSerialize(): array
    {
        $result = [
        ...$this->getJsonLd(),
        'url' => $this->url,
        'alt_text' => $this->altText,
        'width' => $this->width,
        'height' => $this->height,
        'mimetype' => $this->mimeType,
        'sizes' => $this->sizes,
        ];
        return $result;
    }
}
