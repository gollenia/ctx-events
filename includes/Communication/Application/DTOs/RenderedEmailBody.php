<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

final readonly class RenderedEmailBody
{
    public function __construct(
        public string $content,
        public bool $isHtml = false,
    ) {
    }
}
