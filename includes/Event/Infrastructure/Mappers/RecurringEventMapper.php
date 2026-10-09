<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Mappers;

use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Contexis\Events\Event\Domain\RecurringEvent;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceWeekdays;
use Contexis\Events\Event\Infrastructure\RecurringEventMeta;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
use DateTimeImmutable;
use DateTimeZone;
use UnexpectedValueException;

/** Maps the WordPress representation of a series to its booking-free domain model. */
final class RecurringEventMapper
{
    public function map(PostSnapshot $post): RecurringEvent
    {
        $timezone = $this->timezone($post);
        $frequency = $this->frequency($post);
        $weekdays = match ($frequency) {
            RecurrenceFrequency::Daily, RecurrenceFrequency::Yearly => RecurrenceWeekdays::empty(),
            RecurrenceFrequency::Weekly, RecurrenceFrequency::Monthly => $this->weekdays($post),
        };

        return new RecurringEvent(
            id: RecurrenceId::from($post->id),
            status: EventPostStatusMapper::fromPost($post->post_status),
            name: $this->requiredString($post, 'post_title'),
            recurrenceRule: new RecurrenceRule(
                firstOccurrenceStartsAt: $this->requiredDateTime($post, RecurringEventMeta::FIRST_OCCURRENCE_STARTS_AT, $timezone),
                occurrenceDurationSeconds: $this->requiredPositiveInt($post, RecurringEventMeta::OCCURRENCE_DURATION_SECONDS),
                timezone: $timezone,
                frequency: $frequency,
                interval: $this->requiredPositiveInt($post, RecurringEventMeta::INTERVAL),
                weekdays: $weekdays,
                monthday: $frequency === RecurrenceFrequency::Monthly
                    ? $this->positiveOptionalInt($post, RecurringEventMeta::MONTHDAY)
                    : null,
                weekdayPosition: $frequency === RecurrenceFrequency::Monthly
                    ? $this->nonZeroOptionalInt($post, RecurringEventMeta::WEEKDAY_POSITION)
                    : null,
                endsOn: $this->endsOn($post, $timezone),
            ),
            createdAt: $this->requiredDateTime($post, 'post_date', $timezone),
            authorId: new AuthorId($post->getInt('post_author') ?? 0),
            description: $post->getString('post_excerpt'),
            audience: $post->getString(RecurringEventMeta::AUDIENCE),
            locationId: LocationId::from($post->getInt(RecurringEventMeta::LOCATION_ID)),
            personId: PersonId::from($post->getInt(RecurringEventMeta::PERSON_ID)),
            imageId: ImageId::from($post->getThumbnailId()),
        );
    }

    private function timezone(PostSnapshot $post): DateTimeZone
    {
        $name = $this->requiredString($post, RecurringEventMeta::TIMEZONE);

        try {
            return new DateTimeZone($name);
        } catch (\Throwable $exception) {
            throw new UnexpectedValueException(sprintf('Recurring event %d has an invalid timezone.', $post->id), 0, $exception);
        }
    }

    private function frequency(PostSnapshot $post): RecurrenceFrequency
    {
        $frequency = RecurrenceFrequency::tryFrom($this->requiredString($post, RecurringEventMeta::FREQUENCY));
        if ($frequency === null) {
            throw new UnexpectedValueException(sprintf('Recurring event %d has an invalid frequency.', $post->id));
        }

        return $frequency;
    }

    private function weekdays(PostSnapshot $post): RecurrenceWeekdays
    {
        $values = $post->getArray(RecurringEventMeta::WEEKDAYS, []);
        $weekdays = [];

        foreach ($values as $value) {
            if (!is_string($value) || ($weekday = RecurrenceWeekday::tryFrom($value)) === null) {
                throw new UnexpectedValueException(sprintf('Recurring event %d has an invalid weekday.', $post->id));
            }

            $weekdays[] = $weekday;
        }

        return RecurrenceWeekdays::of(...$weekdays);
    }

    private function endsOn(PostSnapshot $post, DateTimeZone $timezone): ?DateTimeImmutable
    {
        if ($post->getString(RecurringEventMeta::ENDS_ON) === null) {
            return null;
        }

        return $this->requiredDateTime($post, RecurringEventMeta::ENDS_ON, $timezone);
    }

    private function requiredDateTime(PostSnapshot $post, string $field, DateTimeZone $timezone): DateTimeImmutable
    {
        $dateTime = $post->getDateTime($field, $timezone);
        if ($dateTime === null) {
            throw new UnexpectedValueException(sprintf('Recurring event %d is missing a valid %s value.', $post->id, $field));
        }

        // Metadata may be an ISO timestamp with a numeric offset. The domain
        // rule deliberately keeps the named recurrence timezone, not that
        // transient offset, so DST-aware generation remains possible.
        return $dateTime->setTimezone($timezone);
    }

    private function requiredPositiveInt(PostSnapshot $post, string $field): int
    {
        $value = $post->getInt($field);
        if ($value === null || $value < 1) {
            throw new UnexpectedValueException(sprintf('Recurring event %d is missing a positive %s value.', $post->id, $field));
        }

        return $value;
    }

    private function positiveOptionalInt(PostSnapshot $post, string $field): ?int
    {
        $value = $post->getInt($field);

        return $value !== null && $value > 0 ? $value : null;
    }

    private function nonZeroOptionalInt(PostSnapshot $post, string $field): ?int
    {
        $value = $post->getInt($field);

        return $value !== null && $value !== 0 ? $value : null;
    }

    private function requiredString(PostSnapshot $post, string $field): string
    {
        $value = $post->getString($field);
        if ($value === null) {
            throw new UnexpectedValueException(sprintf('Recurring event %d is missing %s.', $post->id, $field));
        }

        return $value;
    }
}
