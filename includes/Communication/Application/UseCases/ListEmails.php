<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\UseCases;

use Contexis\Events\Communication\Application\Contracts\EmailTemplatePresetProvider;
use Contexis\Events\Communication\Application\Contracts\EmailTemplateOverrideStore;
use Contexis\Events\Communication\Application\DTOs\MailCriteria;
use Contexis\Events\Communication\Application\DTOs\MailListItem;
use Contexis\Events\Communication\Application\DTOs\MailListResponse;
use Contexis\Events\Communication\Domain\EmailTemplatePreset;
use Contexis\Events\Communication\Domain\ValueObjects\AdminEmailRecipientConfig;

final readonly class ListEmails
{
    public function __construct(
        private EmailTemplatePresetProvider $presetProvider,
        private EmailTemplateOverrideStore $overrideStore,
    ) {
    }

    public function execute(MailCriteria $criteria): MailListResponse
    {
        $overrides = $this->overrideStore->emailTemplateOverrides();
        $items = [];

        foreach ($this->presetProvider->all() as $preset) {
            $item = $this->toListItem($preset, $overrides[$preset->key->value] ?? null);

            if (!$this->matches($item, $criteria)) {
                continue;
            }

            $items[] = $item;
        }

        return MailListResponse::from(...$items);
    }

    /**
     * @param array<string, mixed>|null $override
     */
    private function toListItem(EmailTemplatePreset $preset, ?array $override): MailListItem
    {
        $definition = $preset->definition;
        $isCustomized = is_array($override) && $override !== [];
        $recipientConfig = AdminEmailRecipientConfig::fromArray(
            is_array($override['recipientConfig'] ?? null) ? $override['recipientConfig'] : null,
            $definition->target,
        );

        return new MailListItem(
            key: $preset->key,
            label: $preset->label,
            description: $preset->description,
            trigger: $definition->trigger,
            target: $definition->target,
            source: $isCustomized ? 'database' : 'preset',
            isCustomized: $isCustomized,
            enabled: $this->overrideBool($override, 'enabled', $definition->enabled),
            subject: $this->overrideString($override, 'subject', $definition->subject),
            body: $this->overrideString($override, 'body', $definition->body) ?? '',
            replyTo: $this->overrideString($override, 'replyTo', $definition->replyTo?->toString()),
            recipientConfig: $definition->target->value === 'admin' ? $recipientConfig : null,
        );
    }

    private function matches(MailListItem $item, MailCriteria $criteria): bool
    {
        if ($criteria->target !== null && $item->target !== $criteria->target) {
            return false;
        }

        if ($criteria->trigger !== null && $item->trigger !== $criteria->trigger) {
            return false;
        }

        if ($criteria->search === null || trim($criteria->search) === '') {
            return true;
        }

        $needle = mb_strtolower(trim($criteria->search));
        $haystack = mb_strtolower(implode("\n", array_filter([
            $item->key->value,
            $item->label,
            $item->description,
            $item->subject,
            $item->body,
        ])));

        return str_contains($haystack, $needle);
    }

    /**
     * @param array<string, mixed>|null $override
     */
    private function overrideString(?array $override, string $key, ?string $fallback): ?string
    {
        if (!is_array($override) || !array_key_exists($key, $override)) {
            return $fallback;
        }

        $value = $override[$key];

        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : $fallback;
    }

    /**
     * @param array<string, mixed>|null $override
     */
    private function overrideBool(?array $override, string $key, bool $fallback): bool
    {
        if (!is_array($override) || !array_key_exists($key, $override)) {
            return $fallback;
        }

        return filter_var($override[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $fallback;
    }
}
