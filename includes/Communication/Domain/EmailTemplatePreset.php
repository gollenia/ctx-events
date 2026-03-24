<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain;

use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;

final readonly class EmailTemplatePreset
{
    public function __construct(
        public EmailTemplateKey $key,
        public string $label,
        public string $description,
        public EmailDefinition $definition,
    ) {
    }

    public static function create(
        EmailTemplateKey $key,
        string $label,
        string $description,
        EmailTrigger $trigger,
        EmailTarget $target,
        string $subject,
        string $body,
    ): self {
        return new self(
            key: $key,
            label: $label,
            description: $description,
            definition: new EmailDefinition(
                id: $key->value,
                trigger: $trigger,
                target: $target,
                enabled: true,
                eventId: null,
                gateway: null,
                subject: $subject,
                body: $body,
            ),
        );
    }
}
