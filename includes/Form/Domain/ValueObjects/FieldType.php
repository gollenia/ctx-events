<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

enum FieldType : string
{
    case INPUT = 'input';
    case TEXTAREA = 'textarea';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case HTML = 'html';
	case COUNTRY = 'country';
	case DATE = 'date';
	case NUMBER = 'number';
}
