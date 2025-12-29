<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Enums;

enum SelectVariant: string
{
    case RADIO = 'radio';
    case SELECT = 'select';
    case COMBOBOX = 'combobox';
}
