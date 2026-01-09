<?php

declare(strict_types=1);

namespace Contexis\Events\Platform\Demo; 

readonly class HelloWorldEvent
{
    public function __construct(
        public string $text
    ) {}
}