<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailTemplateOverrideStore;

final class WpEmailTemplateOverrideStore implements EmailTemplateOverrideStore
{
    public const EMAIL_TEMPLATE_OVERRIDES = 'ctx_events_email_template_overrides';

    public function emailTemplateOverrides(): array
    {
        $value = get_option(self::EMAIL_TEMPLATE_OVERRIDES, []);

        if (!is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $key => $override) {
            if (!is_string($key) || !is_array($override)) {
                continue;
            }

            $normalized[$key] = $override;
        }

        return $normalized;
    }

    public function saveEmailTemplateOverride(string $key, array $override): void
    {
        $overrides = $this->emailTemplateOverrides();
        $overrides[$key] = $override;

        update_option(self::EMAIL_TEMPLATE_OVERRIDES, $overrides);
    }

    public function deleteEmailTemplateOverride(string $key): void
    {
        $overrides = $this->emailTemplateOverrides();
        unset($overrides[$key]);

        update_option(self::EMAIL_TEMPLATE_OVERRIDES, $overrides);
    }
}
