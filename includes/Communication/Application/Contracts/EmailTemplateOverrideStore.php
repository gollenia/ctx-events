<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

interface EmailTemplateOverrideStore
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function emailTemplateOverrides(): array;

    /**
     * @param array<string, mixed> $override
     */
    public function saveEmailTemplateOverride(string $key, array $override): void;

    public function deleteEmailTemplateOverride(string $key): void;
}
