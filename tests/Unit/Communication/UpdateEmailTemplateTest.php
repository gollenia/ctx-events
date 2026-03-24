<?php

declare(strict_types=1);

use Contexis\Events\Communication\Application\UseCases\ResetEmailTemplate;
use Contexis\Events\Communication\Application\UseCases\UpdateEmailTemplate;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Infrastructure\DefaultEmailTemplatePresetProvider;
use Tests\Support\FakeEmailTemplateOverrideStore;

test('stores an override when a preset is changed', function () {
    $options = new FakeEmailTemplateOverrideStore();
    $useCase = new UpdateEmailTemplate(
        new DefaultEmailTemplatePresetProvider(),
        $options,
    );

    $updated = $useCase->execute(EmailTemplateKey::BOOKING_PENDING_MANUAL->value, [
        'enabled' => false,
        'subject' => 'Custom subject',
        'body' => 'Custom body',
        'replyTo' => 'team@example.com',
    ]);

    expect($updated)->toBeTrue();
    expect($options->emailTemplateOverrides()[EmailTemplateKey::BOOKING_PENDING_MANUAL->value]['enabled'])->toBeFalse();
    expect($options->emailTemplateOverrides()[EmailTemplateKey::BOOKING_PENDING_MANUAL->value]['subject'])->toBe('Custom subject');
});

test('stores recipient config overrides for admin templates', function () {
    $options = new FakeEmailTemplateOverrideStore();
    $useCase = new UpdateEmailTemplate(
        new DefaultEmailTemplatePresetProvider(),
        $options,
    );

    $updated = $useCase->execute(EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL->value, [
        'enabled' => true,
        'subject' => 'A booking needs manual confirmation',
        'body' => 'A new booking was received and requires manual confirmation or offline payment handling.',
        'recipientConfig' => [
            'sendToBookingAdmin' => true,
            'sendToWpAdmin' => false,
            'customRecipients' => ['ops@example.com'],
        ],
    ]);

    expect($updated)->toBeTrue();
    expect($options->emailTemplateOverrides()[EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL->value]['recipientConfig'])
        ->toBe([
            'sendToEventContact' => false,
            'sendToEventPerson' => false,
            'sendToBookingAdmin' => true,
            'sendToWpAdmin' => false,
            'customRecipients' => ['ops@example.com'],
        ]);
});

test('removes an override when it is reset', function () {
    $options = new FakeEmailTemplateOverrideStore([
        EmailTemplateKey::BOOKING_PENDING_MANUAL->value => [
            'enabled' => false,
        ],
    ]);
    $useCase = new ResetEmailTemplate($options);

    $reset = $useCase->execute(EmailTemplateKey::BOOKING_PENDING_MANUAL->value);

    expect($reset)->toBeTrue();
    expect($options->emailTemplateOverrides())->toBe([]);
});
