<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\UseCases;

use Contexis\Events\Communication\Application\Contracts\EmailTemplateOverrideStore;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;

final readonly class ResetEmailTemplate
{
    public function __construct(
        private EmailTemplateOverrideStore $overrideStore,
    ) {
    }

    public function execute(string $key): bool
    {
        $templateKey = EmailTemplateKey::tryFrom($key);
        if ($templateKey === null) {
            return false;
        }

        $this->overrideStore->deleteEmailTemplateOverride($templateKey->value);

        return true;
    }
}
