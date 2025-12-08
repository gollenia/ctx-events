<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Presentation;

use Contexis\Events\Media\Application\ImageDto;

class ImageResource implements \JsonSerializable
{
    public function __construct(
        public readonly ImageDto $attachment,
    ) {
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
        'url' => $this->attachment->url,
        'alt_text' => $this->attachment->altText,
        'width' => $this->attachment->width,
        'height' => $this->attachment->height,
        'mimetype' => $this->attachment->mimeType,
        'sizes' => $this->attachment->sizes,
        ];
        return $result;
    }
}
