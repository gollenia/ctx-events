<?php

declare(strict_types=1);

use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Infrastructure\DefaultEmailTemplatePresetProvider;

test('provides all v1 communication presets', function () {
    $provider = new DefaultEmailTemplatePresetProvider();
    $presets = $provider->all();

    expect($presets->count())->toBe(11);
    expect($provider->find(EmailTemplateKey::BOOKING_PENDING_MANUAL))->not->toBeNull();
    expect($provider->find(EmailTemplateKey::ADMIN_BOOKING_CREATED_ONLINE))->not->toBeNull();
});

test('marks admin presets with admin target', function () {
    $provider = new DefaultEmailTemplatePresetProvider();
    $preset = $provider->find(EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL);

    expect($preset)->not->toBeNull();
    expect($preset?->definition->target)->toBe(EmailTarget::ADMIN);
});
