<?php

namespace Contexis\Events\Event\Domain;

enum TimeScope: string
{
    case ALL = 'all';
    case FUTURE = 'future';
    case PAST = 'past';
    case TODAY = 'today';
    case TOMORROW = 'tomorrow';
    case WEEK = 'week';
    case THIS_WEEK = 'this-week';
    case THIS_MONTH = 'this-month';
    case NEXT_MONTH = 'next-month';
    case ONE_MONTH = '1-months';
    case TWO_MONTHS = '2-months';
    case THREE_MONTHS = '3-months';
    case SIX_MONTHS = '6-months';
    case TWELVE_MONTHS = '12-months';
    case YEAR = 'year';
}
