<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain;

interface FormRepository
{
    public function get(FormId $formId): Form;
}
