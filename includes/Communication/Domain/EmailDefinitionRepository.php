<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain;

use Contexis\Events\Event\Domain\ValueObjects\EventId;

interface EmailDefinitionRepository
{
    public function save(EmailDefinition $definition): EmailDefinition;

    public function replaceForEvent(EventId $eventId, EmailDefinitionCollection $definitions): EmailDefinitionCollection;

    public function replaceGlobal(EmailDefinitionCollection $definitions): EmailDefinitionCollection;

    public function findById(string $id): ?EmailDefinition;

    public function findByEventId(EventId $eventId): EmailDefinitionCollection;

    public function findGlobal(): EmailDefinitionCollection;

    public function findApplicableForEvent(EventId $eventId): EmailDefinitionCollection;

    public function deleteByEventId(EventId $eventId): void;
}
