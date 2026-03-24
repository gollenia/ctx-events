<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\ValueObjects;

final readonly class EmailAttachment
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $content,
    ) {
    }
}
