<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum EventOrderBy: string
{
	case EVEN_START = 'date';
	case EVENT_TITLE = 'title';
	case BOOKING_START = 'booking_start';
	case BOOKING_ENABLED = 'booking';
	case LOCATION = 'location';
	case PERSON = 'person';
	case PRICE = 'price';
}