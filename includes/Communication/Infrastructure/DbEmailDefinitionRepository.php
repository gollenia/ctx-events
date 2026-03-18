<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\EmailDefinitionCollection;
use Contexis\Events\Communication\Domain\EmailDefinitionRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

final class DbEmailDefinitionRepository implements EmailDefinitionRepository
{
    public function __construct(
        private Database $db,
    ) {
    }

    public function save(EmailDefinition $definition): EmailDefinition
    {
        $table = EmailMigration::getTableName();
        $data = $this->toDatabaseRow($definition);

        if (ctype_digit($definition->id)) {
            $this->db->update($table, $data, ['id' => (int) $definition->id]);

            $saved = $this->findById($definition->id);

            if ($saved === null) {
                throw new \RuntimeException('Failed to update email definition.');
            }

            return $saved;
        }

        $insertId = $this->db->insert($table, $data);

        if ($insertId === false || $insertId === 0) {
            throw new \RuntimeException('Failed to save email definition.');
        }

        $saved = $this->findById((string) $insertId);

        if ($saved === null) {
            throw new \RuntimeException('Saved email definition could not be reloaded.');
        }

        return $saved;
    }

    public function replaceForEvent(EventId $eventId, EmailDefinitionCollection $definitions): EmailDefinitionCollection
    {
        $table = EmailMigration::getTableName();

        $this->db->delete($table, ['event_id' => $eventId->toInt()]);

        foreach ($definitions as $definition) {
            $definitionEventId = $definition->eventId;

            if ($definitionEventId === null || !$definitionEventId->equals($eventId)) {
                throw new \InvalidArgumentException('All email definitions must belong to the event being replaced.');
            }

            $insertId = $this->db->insert($table, $this->toDatabaseRow($definition));

            if ($insertId === false || $insertId === 0) {
                throw new \RuntimeException('Failed to replace email definitions for event.');
            }
        }

        return $this->findByEventId($eventId);
    }

    public function replaceGlobal(EmailDefinitionCollection $definitions): EmailDefinitionCollection
    {
        $table = EmailMigration::getTableName();

        $this->db->query("DELETE FROM {$table} WHERE event_id IS NULL");

        foreach ($definitions as $definition) {
            if ($definition->eventId !== null) {
                throw new \InvalidArgumentException('Global email definitions must not belong to an event.');
            }

            $insertId = $this->db->insert($table, $this->toDatabaseRow($definition));

            if ($insertId === false || $insertId === 0) {
                throw new \RuntimeException('Failed to replace global email definitions.');
            }
        }

        return $this->findGlobal();
    }

    public function findById(string $id): ?EmailDefinition
    {
        if (!ctype_digit($id)) {
            return null;
        }

        $table = EmailMigration::getTableName();
        $sql = $this->db->prepare("SELECT * FROM {$table} WHERE id = %d", (int) $id);
        $row = $this->db->getRow($sql, DatabaseOutput::ARRAY_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        return $this->mapRow($row);
    }

    public function findByEventId(EventId $eventId): EmailDefinitionCollection
    {
        $table = EmailMigration::getTableName();
        $sql = $this->db->prepare(
            "SELECT * FROM {$table} WHERE event_id = %d ORDER BY id ASC",
            $eventId->toInt()
        );
        $rows = $this->db->getResults($sql, DatabaseOutput::ARRAY_ASSOC);

        return EmailDefinitionCollection::from(
            ...array_map($this->mapRow(...), $rows)
        );
    }

    public function findGlobal(): EmailDefinitionCollection
    {
        $table = EmailMigration::getTableName();
        $rows = $this->db->getResults(
            "SELECT * FROM {$table} WHERE event_id IS NULL ORDER BY id ASC",
            DatabaseOutput::ARRAY_ASSOC
        );

        return EmailDefinitionCollection::from(
            ...array_map($this->mapRow(...), $rows)
        );
    }

    public function findApplicableForEvent(EventId $eventId): EmailDefinitionCollection
    {
        $table = EmailMigration::getTableName();
        $sql = $this->db->prepare(
            "SELECT * FROM {$table}
            WHERE event_id = %d OR event_id IS NULL
            ORDER BY CASE WHEN event_id = %d THEN 0 ELSE 1 END, id ASC",
            $eventId->toInt(),
            $eventId->toInt(),
        );
        $rows = $this->db->getResults($sql, DatabaseOutput::ARRAY_ASSOC);

        return EmailDefinitionCollection::from(
            ...array_map($this->mapRow(...), $rows)
        );
    }

    public function deleteByEventId(EventId $eventId): void
    {
        $table = EmailMigration::getTableName();
        $this->db->delete($table, ['event_id' => $eventId->toInt()]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): EmailDefinition
    {
        return EmailDefinitionMapper::map($row);
    }

    /**
     * @return array<string, int|string|null>
     */
    private function toDatabaseRow(EmailDefinition $definition): array
    {
        return [
            'event_id' => $definition->eventId?->toInt(),
            EmailMigration::TRIGGER => $definition->trigger->value,
            EmailMigration::TARGET => $definition->target->value,
            'enabled' => $definition->enabled ? 1 : 0,
            'gateway' => $definition->gateway,
            'subject' => $definition->subject,
            'body' => $definition->body,
            'reply_to' => $definition->replyTo?->toString(),
        ];
    }
}
