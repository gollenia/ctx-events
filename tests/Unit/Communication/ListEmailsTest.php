<?php

declare(strict_types=1);

use Contexis\Events\Communication\Application\DTOs\MailCriteria;
use Contexis\Events\Communication\Application\UseCases\ListEmails;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Infrastructure\DefaultEmailTemplatePresetProvider;
use Tests\Support\FakeEmailTemplateOverrideStore;

test('lists preset emails by default', function () {
    $useCase = new ListEmails(
        new DefaultEmailTemplatePresetProvider(),
        new FakeEmailTemplateOverrideStore(),
    );

    $result = $useCase->execute(new MailCriteria());
    $adminTemplate = array_values(array_filter(
        $result->toArray(),
        static fn ($item) => $item->key === EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL
    ))[0] ?? null;

    expect($result->count())->toBe(11);
    expect($adminTemplate)->not->toBeNull();
    assert($adminTemplate instanceof \Contexis\Events\Communication\Application\DTOs\MailListItem);
    expect($adminTemplate->recipientConfig?->sendToWpAdmin)->toBeTrue();
});

test('marks overridden presets as customized and uses override content', function () {
    $useCase = new ListEmails(
        new DefaultEmailTemplatePresetProvider(),
        new FakeEmailTemplateOverrideStore([
            EmailTemplateKey::BOOKING_PENDING_MANUAL->value => [
                'subject' => 'Custom subject',
                'enabled' => false,
            ],
            EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL->value => [
                'recipientConfig' => [
                    'sendToBookingAdmin' => true,
                    'sendToWpAdmin' => false,
                    'customRecipients' => ['ops@example.com'],
                ],
            ],
        ]),
    );

    $result = $useCase->execute(new MailCriteria());
    $items = $result->toArray();
    $first = array_values(array_filter(
        $items,
        static fn ($item) => $item->key === EmailTemplateKey::BOOKING_PENDING_MANUAL
    ))[0] ?? null;

    expect($first)->not->toBeNull();
    assert($first instanceof \Contexis\Events\Communication\Application\DTOs\MailListItem);
    expect($first->isCustomized)->toBeTrue();
    expect($first->source)->toBe('database');
    expect($first->subject)->toBe('Custom subject');
    expect($first->enabled)->toBeFalse();

    $admin = array_values(array_filter(
        $items,
        static fn ($item) => $item->key === EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL
    ))[0] ?? null;

    expect($admin)->not->toBeNull();
    assert($admin instanceof \Contexis\Events\Communication\Application\DTOs\MailListItem);
    expect($admin->recipientConfig?->sendToBookingAdmin)->toBeTrue();
    expect($admin->recipientConfig?->sendToWpAdmin)->toBeFalse();
    expect($admin->recipientConfig?->customRecipients)->toBe(['ops@example.com']);
});

test('filters emails by target and search', function () {
    $useCase = new ListEmails(
        new DefaultEmailTemplatePresetProvider(),
        new FakeEmailTemplateOverrideStore(),
    );

    $result = $useCase->execute(new MailCriteria(
        search: 'online',
        target: EmailTarget::ADMIN,
    ));

    expect($result->count())->toBe(1);
    $first = $result->first();
    expect($first)->not->toBeNull();
    assert($first instanceof \Contexis\Events\Communication\Application\DTOs\MailListItem);
    expect($first->key)->toBe(EmailTemplateKey::ADMIN_BOOKING_CREATED_ONLINE);
});
