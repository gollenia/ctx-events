<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Presentation\Resources;

use Contexis\Events\Communication\Application\DTOs\MailListItem;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MailTemplate')]
final readonly class MailListItemResource implements Resource
{
    /**
     * @param array{
     *   sendToEventContact: bool,
     *   sendToEventPerson: bool,
     *   sendToBookingAdmin: bool,
     *   sendToWpAdmin: bool,
     *   customRecipients: list<string>
     * }|null $recipientConfig
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $description,
        public string $trigger,
        public string $target,
        public string $source,
        public bool $isCustomized,
        public bool $enabled,
        public ?string $subject,
        public string $body,
        public ?string $replyTo = null,
        public ?array $recipientConfig = null,
    ) {
    }

    public static function fromDTO(MailListItem $item): self
    {
        return new self(
            key: $item->key->value,
            label: $item->label,
            description: $item->description,
            trigger: $item->trigger->value,
            target: $item->target->value,
            source: $item->source,
            isCustomized: $item->isCustomized,
            enabled: $item->enabled,
            subject: $item->subject,
            body: $item->body,
            replyTo: $item->replyTo,
            recipientConfig: $item->recipientConfig?->toArray(),
        );
    }

    /**
     * @return array<string, string|bool|array<int|string, mixed>|null>
     */
    public function jsonSerialize(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'trigger' => $this->trigger,
            'target' => $this->target,
            'source' => $this->source,
            'isCustomized' => $this->isCustomized,
            'enabled' => $this->enabled,
            'subject' => $this->subject,
            'body' => $this->body,
            'replyTo' => $this->replyTo,
            'recipientConfig' => $this->recipientConfig,
        ];
    }
}
