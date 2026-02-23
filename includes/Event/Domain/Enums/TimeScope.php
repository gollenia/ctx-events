<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum TimeScope: string
{
    case ALL = 'all';
    case FUTURE = 'future';
    case PAST = 'past';
    case TODAY = 'today';
    case TOMORROW = 'tomorrow';
    case ONE_WEEK = 'one-week';
    case THIS_WEEK = 'this-week';
    case THIS_MONTH = 'this-month';
    case NEXT_MONTH = 'next-month';
    case ONE_MONTH = '1-months';
    case TWO_MONTHS = '2-months';
    case THREE_MONTHS = '3-months';
    case THIS_YEAR = 'this-year';
	case ONE_YEAR = '1-year';
}
