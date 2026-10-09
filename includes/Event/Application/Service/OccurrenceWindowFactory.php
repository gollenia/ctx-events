<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Translates a request-level time scope into a finite occurrence window.
 */
final class OccurrenceWindowFactory
{
    public function fromScope(TimeScope $scope, DateTimeImmutable $now): OccurrenceWindow
    {
        $startOfToday = $now->setTime(0, 0, 0);

        [$startsAt, $endsAt] = match ($scope) {
            TimeScope::FUTURE => [$now, $now->modify('+12 months')],
            TimeScope::TODAY => [$startOfToday, $startOfToday->modify('+1 day')],
            TimeScope::TOMORROW => [$startOfToday->modify('+1 day'), $startOfToday->modify('+2 days')],
            TimeScope::ONE_WEEK => [$now, $now->modify('+1 week')],
            TimeScope::THIS_WEEK => [
                $startOfToday->modify('monday this week'),
                $startOfToday->modify('monday next week'),
            ],
            TimeScope::THIS_MONTH => [
                $startOfToday->modify('first day of this month'),
                $startOfToday->modify('first day of next month'),
            ],
            TimeScope::NEXT_MONTH => [
                $startOfToday->modify('first day of next month'),
                $startOfToday->modify('first day of +2 months'),
            ],
            TimeScope::ONE_MONTH => [$now, $now->modify('+1 month')],
            TimeScope::TWO_MONTHS => [$now, $now->modify('+2 months')],
            TimeScope::THREE_MONTHS => [$now, $now->modify('+3 months')],
            TimeScope::THIS_YEAR => [
                $startOfToday->modify('first day of january this year'),
                $startOfToday->modify('first day of january next year'),
            ],
            TimeScope::ONE_YEAR => [$now, $now->modify('+12 months')],
            TimeScope::ALL, TimeScope::PAST => throw new InvalidArgumentException(
                sprintf('The "%s" scope is not available for recurring occurrences.', $scope->value),
            ),
        };

        return new OccurrenceWindow($startsAt, $endsAt);
    }
}
