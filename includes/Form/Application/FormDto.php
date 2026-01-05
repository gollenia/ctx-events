<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Application;

class FormDto
{
    public function __construct(
        public string $id,
        public string $title,
        public string $description,
        public string $type,
    ) {
    }
}