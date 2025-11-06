<?php

namespace Contexis\Events\Presentation\Resources;

use Contexis\Events\Application\DTO as DTO;
use JsonSerializable;

class AttachmentResource implements JsonSerializable
{
    public function __construct(
        public readonly DTO\Attachment $attachment,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->attachment->url,
            'alt_text' => $this->attachment->alt_text,
            'width' => $this->attachment->width,
            'height' => $this->attachment->height,
            'mimeType' => $this->attachment->mimeType,
            'sizes' => $this->attachment->sizes ? [
                'thumbnail' => $this->attachment->sizes->thumbnail,
                'medium' => $this->attachment->sizes->medium,
                'large' => $this->attachment->sizes->large,
                'original' => $this->attachment->sizes->original,
            ] : null,
        ];
    }
}
