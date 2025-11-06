<?php

namespace Contexis\Events\Presentation\Resources;

use Contexis\Events\Application\DTO as DTO;
use Contexis\Events\Presentation\Services\Links;

class ImageResource implements \JsonSerializable
{
    public function __construct(
        public readonly DTO\Image $attachment,
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
        'alt_text' => $this->attachment->alt_text,
        'width' => $this->attachment->width,
        'height' => $this->attachment->height,
        'mimetype' => $this->attachment->mimeType,
        'sizes' => $this->attachment->sizes,
        ];
        return $result;
    }
}
