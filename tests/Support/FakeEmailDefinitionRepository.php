<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\EmailDefinitionCollection;
use Contexis\Events\Communication\Domain\EmailDefinitionRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

final class FakeEmailDefinitionRepository implements EmailDefinitionRepository
{
    public function __construct(
        private EmailDefinitionCollection $definitions,
    ) {
    }

    public function save(EmailDefinition $definition): EmailDefinition
    {
        $items = $this->definitions->toArray();
        $items[] = $definition;
        $this->definitions = EmailDefinitionCollection::from(...$items);

        return $definition;
    }

    public function replaceForEvent(EventId $eventId, EmailDefinitionCollection $definitions): EmailDefinitionCollection
    {
        $retained = array_filter(
            $this->definitions->toArray(),
            static fn (EmailDefinition $definition): bool => $definition->eventId === null || !$definition->eventId->equals($eventId)
        );

        $this->definitions = EmailDefinitionCollection::from(...[...$retained, ...$definitions->toArray()]);

        return $definitions;
    }

    public function replaceGlobal(EmailDefinitionCollection $definitions): EmailDefinitionCollection
    {
        $retained = array_filter(
            $this->definitions->toArray(),
            static fn (EmailDefinition $definition): bool => $definition->eventId !== null
        );

        $this->definitions = EmailDefinitionCollection::from(...[...$retained, ...$definitions->toArray()]);

        return $definitions;
    }

    public function findById(string $id): ?EmailDefinition
    {
        foreach ($this->definitions as $definition) {
            if ($definition->id === $id) {
                return $definition;
            }
        }

        return null;
    }

    public function findByEventId(EventId $eventId): EmailDefinitionCollection
    {
        return EmailDefinitionCollection::from(
            ...array_values(array_filter(
                $this->definitions->toArray(),
                static fn (EmailDefinition $definition): bool => $definition->eventId !== null && $definition->eventId->equals($eventId)
            ))
        );
    }

    public function findGlobal(): EmailDefinitionCollection
    {
        return EmailDefinitionCollection::from(
            ...array_values(array_filter(
                $this->definitions->toArray(),
                static fn (EmailDefinition $definition): bool => $definition->eventId === null
            ))
        );
    }

    public function findApplicableForEvent(EventId $eventId): EmailDefinitionCollection
    {
        return EmailDefinitionCollection::from(
            ...array_values(array_filter(
                $this->definitions->toArray(),
                static fn (EmailDefinition $definition): bool => $definition->eventId === null || $definition->eventId->equals($eventId)
            ))
        );
    }

    public function deleteByEventId(EventId $eventId): void
    {
        $this->definitions = EmailDefinitionCollection::from(
            ...array_values(array_filter(
                $this->definitions->toArray(),
                static fn (EmailDefinition $definition): bool => $definition->eventId === null || !$definition->eventId->equals($eventId)
            ))
        );
    }
}
