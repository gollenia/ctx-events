<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\RecurringOccurrenceDetacher;
use Contexis\Events\Event\Domain\Enums\OccurrenceExceptionType;
use Contexis\Events\Event\Domain\OccurrenceGenerator;
use Contexis\Events\Event\Domain\RecurringEventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use DomainException;
use RuntimeException;

/** WordPress persistence adapter for the atomic-in-intent detach operation. */
final readonly class WpRecurringOccurrenceDetacher implements RecurringOccurrenceDetacher
{
    public function __construct(
        private RecurringEventRepository $recurringEvents,
        private OccurrenceGenerator $occurrenceGenerator,
    ) {
    }

    public function detach(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): EventId
    {
        $series = $this->recurringEvents->find($recurrenceId);
        if ($series === null) {
            throw new DomainException('Recurring event not found.');
        }

        $existing = $this->exceptions($recurrenceId);
        $key = (string) $occurrenceKey;
        if (isset($existing[$key])) {
            return $this->alreadyDetached($existing[$key]);
        }

        $occurrence = $this->scheduledOccurrence($series, $occurrenceKey);
        $postId = $this->createEvent($series, $occurrenceKey, $occurrence->startsAt, $occurrence->endsAt);

        $existing[$key] = ['type' => OccurrenceExceptionType::Detached->value, 'eventPostId' => $postId];
        if (update_post_meta($recurrenceId->toInt(), RecurringEventMeta::EXCEPTIONS, $existing) === false) {
            wp_delete_post($postId, true);
            throw new RuntimeException('Could not save the detached occurrence exception.');
        }

        return new EventId($postId);
    }

    private function alreadyDetached(mixed $exception): EventId
    {
        if (!is_array($exception) || !is_string($exception['type'] ?? null)) {
            throw new RuntimeException('Recurring event contains malformed exception metadata.');
        }
        if ($exception['type'] === OccurrenceExceptionType::Cancelled->value) {
            throw new DomainException('A cancelled occurrence cannot be detached. Remove its exception first.');
        }
        if ($exception['type'] !== OccurrenceExceptionType::Detached->value || !is_int($exception['eventPostId'] ?? null)) {
            throw new RuntimeException('Recurring event contains malformed detached exception metadata.');
        }

        return new EventId($exception['eventPostId']);
    }

    private function scheduledOccurrence(\Contexis\Events\Event\Domain\RecurringEvent $series, OccurrenceKey $key): \Contexis\Events\Event\Domain\Occurrence
    {
        $start = $key->scheduledStartsAt->setTimezone($series->recurrenceRule->timezone);
        $window = new OccurrenceWindow($start->setTime(0, 0), $start->setTime(0, 0)->modify('+1 day'));
        foreach ($this->occurrenceGenerator->generate($series->id, $series->recurrenceRule, $window) as $occurrence) {
            if ($occurrence->key->equals($key)) {
                return $occurrence;
            }
        }

        throw new DomainException('The occurrence is not scheduled by this recurring event.');
    }

    private function createEvent(\Contexis\Events\Event\Domain\RecurringEvent $series, OccurrenceKey $key, \DateTimeImmutable $startsAt, \DateTimeImmutable $endsAt): int
    {
        $source = get_post($series->id->toInt());
        if (!$source instanceof \WP_Post) {
            throw new RuntimeException('Recurring event post not found.');
        }
        $postId = wp_insert_post([
            'post_type' => EventPost::POST_TYPE,
            'post_status' => $series->status->value,
            'post_title' => $source->post_title,
            'post_content' => $source->post_content,
            'post_excerpt' => $source->post_excerpt,
            'post_author' => $series->authorId->toInt(),
        ], true);
        if (is_wp_error($postId)) {
            throw new RuntimeException($postId->get_error_message());
        }
        $id = (int) $postId;
        foreach ([EventTaxonomy::CATEGORIES, EventTaxonomy::TAGS] as $taxonomy) {
            $terms = wp_get_object_terms($series->id->toInt(), $taxonomy, ['fields' => 'ids']);
            if (!is_wp_error($terms) && is_array($terms)) {
                wp_set_object_terms($id, $terms, $taxonomy, false);
            }
        }
        $meta = [
            EventMeta::EVENT_START => $startsAt->format(DATE_ATOM),
            EventMeta::EVENT_END => $endsAt->format(DATE_ATOM),
            EventMeta::BOOKING_ENABLED => false,
            EventMeta::RECURRENCE_ID => $series->id->toInt(),
            EventMeta::RECURRENCE_OCCURRENCE_KEY => (string) $key,
            EventMeta::IS_DETACHED => true,
            EventMeta::AUDIENCE => $series->audience,
            EventMeta::LOCATION_ID => $series->locationId?->toInt(),
            EventMeta::PERSON_ID => $series->personId?->toInt(),
        ];
        foreach ($meta as $name => $value) {
            if ($value !== null && update_post_meta($id, $name, $value) === false) {
                wp_delete_post($id, true);
                throw new RuntimeException('Could not create detached event metadata.');
            }
        }

        return $id;
    }

    /** @return array<string, mixed> */
    private function exceptions(RecurrenceId $recurrenceId): array
    {
        $exceptions = get_post_meta($recurrenceId->toInt(), RecurringEventMeta::EXCEPTIONS, true);
        if ($exceptions === '' || $exceptions === null) {
            return [];
        }
        if (!is_array($exceptions)) {
            throw new RuntimeException('Recurring event contains malformed exception metadata.');
        }

        return $exceptions;
    }
}
