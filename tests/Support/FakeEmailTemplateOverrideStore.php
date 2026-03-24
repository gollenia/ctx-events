<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Communication\Application\Contracts\EmailTemplateOverrideStore;

final class FakeEmailTemplateOverrideStore implements EmailTemplateOverrideStore
{
    /**
     * @param array<string, array<string, mixed>> $overrides
     */
    public function __construct(
        private array $overrides = [],
    ) {
    }

    public function emailTemplateOverrides(): array
    {
        return $this->overrides;
    }

    public function saveEmailTemplateOverride(string $key, array $override): void
    {
        $overrides = $this->overrides;
        $overrides[$key] = $override;
        $this->overrides = $overrides;
    }

    public function deleteEmailTemplateOverride(string $key): void
    {
        $overrides = $this->overrides;
        unset($overrides[$key]);
        $this->overrides = $overrides;
    }
}
