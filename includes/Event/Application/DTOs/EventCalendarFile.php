<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

final readonly class EventCalendarFile
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $content,
    ) {
    }
}
