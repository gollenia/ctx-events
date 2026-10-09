<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\UseCases\CancelRecurringOccurrence;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;

test('cancels an occurrence belonging to its recurring event', function (): void {
    $recurrenceId = new RecurrenceId(42);
    $occurrenceKey = OccurrenceKey::from($recurrenceId, new DateTimeImmutable('2026-11-10 19:00:00+01:00'));
    $canceller = new class implements \Contexis\Events\Event\Application\Contracts\RecurringOccurrenceCanceller {
        /** @var list<array{recurrenceId: RecurrenceId, occurrenceKey: OccurrenceKey}> */
        public array $calls = [];

        public function cancel(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): void
        {
            $this->calls[] = compact('recurrenceId', 'occurrenceKey');
        }
    };

    (new CancelRecurringOccurrence($canceller))->execute($recurrenceId, $occurrenceKey);

    expect($canceller->calls)->toHaveCount(1)
        ->and($canceller->calls[0]['recurrenceId']->equals($recurrenceId))->toBeTrue()
        ->and($canceller->calls[0]['occurrenceKey']->equals($occurrenceKey))->toBeTrue();
});

test('does not cancel an occurrence from another recurring event', function (): void {
    $canceller = new class implements \Contexis\Events\Event\Application\Contracts\RecurringOccurrenceCanceller {
        public bool $wasCalled = false;

        public function cancel(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): void
        {
            $this->wasCalled = true;
        }
    };

    $useCase = new CancelRecurringOccurrence($canceller);
    $occurrenceKey = OccurrenceKey::from(new RecurrenceId(43), new DateTimeImmutable('2026-11-10 19:00:00+01:00'));

    expect(fn () => $useCase->execute(new RecurrenceId(42), $occurrenceKey))
        ->toThrow(DomainException::class, 'The occurrence does not belong to this recurring event.')
        ->and($canceller->wasCalled)->toBeFalse();
});
