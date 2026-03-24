<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain;

use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class EmailTemplatePresetCollection extends Collection
{
    public static function from(EmailTemplatePreset ...$presets): self
    {
        return new self($presets);
    }

    public function findByKey(EmailTemplateKey $key): ?EmailTemplatePreset
    {
        foreach ($this->items as $preset) {
            if ($preset->key === $key) {
                return $preset;
            }
        }

        return null;
    }

    /** @return list<EmailTemplatePreset> */
    public function findByTrigger(EmailTrigger $trigger): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (EmailTemplatePreset $preset): bool => $preset->definition->trigger === $trigger,
        ));
    }
}
