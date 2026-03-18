<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Infrastructure\Contracts\DatabaseMapper;

final class EmailDefinitionMapper implements DatabaseMapper
{
    /**
     * @param array<string, mixed> $data
     */
    public static function map(array $data): object
    {
        $trigger = EmailTrigger::tryFrom((string) ($data[EmailMigration::TRIGGER] ?? ''));
        $target = EmailTarget::tryFrom((string) ($data[EmailMigration::TARGET] ?? ''));

        if ($trigger === null) {
            throw new \InvalidArgumentException('Cannot map email definition without valid trigger.');
        }

        if ($target === null) {
            throw new \InvalidArgumentException('Cannot map email definition without valid target.');
        }

        $eventId = isset($data['event_id']) ? EventId::from((int) $data['event_id']) : null;
        $replyTo = Email::tryFrom(isset($data['reply_to']) ? (string) $data['reply_to'] : null);
        $subject = self::nullableString($data['subject'] ?? null);
        $gateway = self::nullableString($data['gateway'] ?? null);

        return new EmailDefinition(
            id: (string) ($data['id'] ?? ''),
            trigger: $trigger,
            target: $target,
            enabled: (bool) ($data['enabled'] ?? false),
            eventId: $eventId,
            gateway: $gateway,
            subject: $subject,
            body: (string) ($data['body'] ?? ''),
            replyTo: $replyTo,
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
