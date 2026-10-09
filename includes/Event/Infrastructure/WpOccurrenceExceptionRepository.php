<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Domain\Enums\OccurrenceExceptionType;
use Contexis\Events\Event\Domain\OccurrenceException;
use Contexis\Events\Event\Domain\OccurrenceExceptionCollection;
use Contexis\Events\Event\Domain\OccurrenceExceptionRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceIds;
use UnexpectedValueException;

/** Reads exceptional slots from the owning recurring-event post metadata. */
final class WpOccurrenceExceptionRepository implements OccurrenceExceptionRepository
{
    public function findForRecurringEvents(RecurrenceIds $recurringEventIds, OccurrenceWindow $window): OccurrenceExceptionCollection
    {
        if ($recurringEventIds->isEmpty()) {
            return OccurrenceExceptionCollection::from();
        }

        $query = new \WP_Query([
            'post_type' => RecurringEventPost::POST_TYPE,
            'post__in' => array_map(static fn ($id): int => $id->toInt(), $recurringEventIds->toArray()),
            'post_status' => 'any',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'fields' => 'ids',
        ]);

        $exceptions = [];
        foreach ($query->posts as $postId) {
            $seriesId = new \Contexis\Events\Event\Domain\ValueObjects\RecurrenceId((int) $postId);
            $stored = get_post_meta((int) $postId, RecurringEventMeta::EXCEPTIONS, true);

            if ($stored === '' || $stored === null) {
                continue;
            }

            if (!is_array($stored)) {
                throw new UnexpectedValueException(sprintf('Recurring event %d has malformed exception metadata.', $postId));
            }

            foreach ($stored as $serializedKey => $data) {
                $occurrenceKey = $this->occurrenceKey((int) $postId, $serializedKey, $seriesId);
                if ($occurrenceKey->scheduledStartsAt < $window->startsAt || $occurrenceKey->scheduledStartsAt >= $window->endsAt) {
                    continue;
                }

                $exceptions[] = $this->exception((int) $postId, $occurrenceKey, $data);
            }
        }

        return OccurrenceExceptionCollection::from(...$exceptions);
    }

    private function occurrenceKey(int $postId, mixed $serializedKey, \Contexis\Events\Event\Domain\ValueObjects\RecurrenceId $seriesId): OccurrenceKey
    {
        if (!is_string($serializedKey)) {
            throw new UnexpectedValueException(sprintf('Recurring event %d has a non-string exception key.', $postId));
        }

        try {
            $key = OccurrenceKey::fromString($serializedKey);
        } catch (\Throwable $exception) {
            throw new UnexpectedValueException(sprintf('Recurring event %d has an invalid exception key.', $postId), 0, $exception);
        }

        if (!$key->recurringEventId->equals($seriesId)) {
            throw new UnexpectedValueException(sprintf('Recurring event %d contains an exception for another series.', $postId));
        }

        return $key;
    }

    private function exception(int $postId, OccurrenceKey $key, mixed $data): OccurrenceException
    {
        if (!is_array($data) || !isset($data['type']) || !is_string($data['type'])) {
            throw new UnexpectedValueException(sprintf('Recurring event %d has malformed exception data.', $postId));
        }

        return match (OccurrenceExceptionType::tryFrom($data['type'])) {
            OccurrenceExceptionType::Cancelled => OccurrenceException::cancelled($key),
            OccurrenceExceptionType::Detached => $this->detachedException($postId, $key, $data),
            default => throw new UnexpectedValueException(sprintf('Recurring event %d has an unknown exception type.', $postId)),
        };
    }

    /** @param array<string, mixed> $data */
    private function detachedException(int $postId, OccurrenceKey $key, array $data): OccurrenceException
    {
        $eventPostId = $data['eventPostId'] ?? null;
        if (!is_int($eventPostId) || $eventPostId < 1) {
            throw new UnexpectedValueException(sprintf('Recurring event %d has a detached exception without an event post ID.', $postId));
        }

        return OccurrenceException::detached($key, new EventId($eventPostId));
    }
}
