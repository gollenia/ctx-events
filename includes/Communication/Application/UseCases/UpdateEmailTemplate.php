<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\UseCases;

use Contexis\Events\Communication\Application\Contracts\EmailTemplatePresetProvider;
use Contexis\Events\Communication\Application\Contracts\EmailTemplateOverrideStore;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\ValueObjects\AdminEmailRecipientConfig;

final readonly class UpdateEmailTemplate
{
    public function __construct(
        private EmailTemplatePresetProvider $presetProvider,
        private EmailTemplateOverrideStore $overrideStore,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(string $key, array $payload): bool
    {
        $templateKey = EmailTemplateKey::tryFrom($key);
        if ($templateKey === null) {
            return false;
        }

        $preset = $this->presetProvider->find($templateKey);
        if ($preset === null) {
            return false;
        }

        $override = [];

        $enabled = filter_var($payload['enabled'] ?? $preset->definition->enabled, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
            ?? $preset->definition->enabled;
        if ($enabled !== $preset->definition->enabled) {
            $override['enabled'] = $enabled;
        }

        $subject = $this->nullableString($payload['subject'] ?? $preset->definition->subject);
        if ($subject !== $preset->definition->subject) {
            $override['subject'] = $subject;
        }

        $body = $this->stringOrFallback($payload['body'] ?? null, $preset->definition->body);
        if ($body !== $preset->definition->body) {
            $override['body'] = $body;
        }

        $replyTo = $this->nullableString($payload['replyTo'] ?? $preset->definition->replyTo?->toString());
        if ($replyTo !== $preset->definition->replyTo?->toString()) {
            $override['replyTo'] = $replyTo;
        }

        $recipientConfig = AdminEmailRecipientConfig::fromArray(
            is_array($payload['recipientConfig'] ?? null) ? $payload['recipientConfig'] : null,
            $preset->definition->target,
        );
        $defaultRecipientConfig = AdminEmailRecipientConfig::defaultsFor($preset->definition->target);

        if (!$recipientConfig->equals($defaultRecipientConfig)) {
            $override['recipientConfig'] = $recipientConfig->toArray();
        }

        if ($override === []) {
            $this->overrideStore->deleteEmailTemplateOverride($templateKey->value);

            return true;
        }

        $this->overrideStore->saveEmailTemplateOverride($templateKey->value, $override);

        return true;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function stringOrFallback(mixed $value, string $fallback): string
    {
        if (!is_scalar($value)) {
            return $fallback;
        }

        return (string) $value;
    }
}
