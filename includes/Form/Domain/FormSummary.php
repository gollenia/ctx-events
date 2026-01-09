<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

use Contexis\Events\Form\Domain\Enums\FormType;

class FormSummary
{
    public function __construct(
        public FormId $id,
        public FormType $type,
        public string $title,
        public string $description,
        public int $usageCount
    ) {
    }
}