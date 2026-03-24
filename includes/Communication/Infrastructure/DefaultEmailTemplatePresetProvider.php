<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailTemplatePresetProvider;
use Contexis\Events\Communication\Domain\EmailTemplatePreset;
use Contexis\Events\Communication\Domain\EmailTemplatePresetCollection;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;

final readonly class DefaultEmailTemplatePresetProvider implements EmailTemplatePresetProvider
{
    public function all(): EmailTemplatePresetCollection
    {
        return EmailTemplatePresetCollection::from(
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_PENDING_MANUAL,
                label: $this->translate('Booking pending manual'),
                description: $this->translate('Sent to participants when a booking was received but still requires manual confirmation or offline payment.'),
                trigger: EmailTrigger::BOOKING_PENDING_MANUAL,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('We received your booking'),
                body: $this->translate('Your booking has been received and is currently awaiting manual confirmation.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CREATED_ONLINE,
                label: $this->translate('Booking created online'),
                description: $this->translate('Sent to participants when an online payment booking has been started.'),
                trigger: EmailTrigger::BOOKING_CREATED_ONLINE,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your booking was created'),
                body: $this->translate('Your booking was created and the online payment process has started.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CONFIRMED_MANUAL,
                label: $this->translate('Booking confirmed manually'),
                description: $this->translate('Sent to participants when a booking was manually confirmed.'),
                trigger: EmailTrigger::BOOKING_CONFIRMED_MANUAL,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your booking is confirmed'),
                body: $this->translate('Your booking has been confirmed. We look forward to seeing you.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CONFIRMED_ONLINE,
                label: $this->translate('Booking confirmed online'),
                description: $this->translate('Sent to participants when an online payment completed successfully.'),
                trigger: EmailTrigger::BOOKING_CONFIRMED_ONLINE,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your booking is confirmed'),
                body: $this->translate('Your online payment was successful and your booking is now confirmed.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_OFFLINE_EXPIRING,
                label: $this->translate('Offline payment expiring'),
                description: $this->translate('Reminder that an offline payment must be completed soon.'),
                trigger: EmailTrigger::BOOKING_OFFLINE_EXPIRING,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your booking is about to expire'),
                body: $this->translate('Please complete your payment soon or the reserved spaces may be released.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_OFFLINE_EXPIRED,
                label: $this->translate('Offline payment expired'),
                description: $this->translate('Sent when an unpaid offline booking expired and the spaces were released.'),
                trigger: EmailTrigger::BOOKING_OFFLINE_EXPIRED,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your booking has expired'),
                body: $this->translate('Your booking expired because the offline payment was not completed in time.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_PAYMENT_FAILED,
                label: $this->translate('Payment failed'),
                description: $this->translate('Sent to participants when an online payment failed.'),
                trigger: EmailTrigger::BOOKING_PAYMENT_FAILED,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your payment failed'),
                body: $this->translate('Unfortunately, your online payment could not be completed.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_DENIED,
                label: $this->translate('Booking denied'),
                description: $this->translate('Sent to participants when a booking was denied.'),
                trigger: EmailTrigger::BOOKING_DENIED,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your booking was denied'),
                body: $this->translate('Unfortunately, your booking could not be accepted.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CANCELLED,
                label: $this->translate('Booking cancelled'),
                description: $this->translate('Sent to participants when a booking was cancelled.'),
                trigger: EmailTrigger::BOOKING_CANCELLED,
                target: EmailTarget::CUSTOMER,
                subject: $this->translate('Your booking was cancelled'),
                body: $this->translate('Your booking has been cancelled.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL,
                label: $this->translate('Admin: booking pending manual'),
                description: $this->translate('Sent to admins when a new booking needs manual handling.'),
                trigger: EmailTrigger::BOOKING_PENDING_MANUAL,
                target: EmailTarget::ADMIN,
                subject: $this->translate('A booking needs manual confirmation'),
                body: $this->translate('A new booking was received and requires manual confirmation or offline payment handling.'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::ADMIN_BOOKING_CREATED_ONLINE,
                label: $this->translate('Admin: booking created online'),
                description: $this->translate('Sent to admins when a new online booking was created.'),
                trigger: EmailTrigger::BOOKING_CREATED_ONLINE,
                target: EmailTarget::ADMIN,
                subject: $this->translate('A new online booking was created'),
                body: $this->translate('A new booking with online payment was created.'),
            ),
        );
    }

    public function find(EmailTemplateKey $key): ?EmailTemplatePreset
    {
        return $this->all()->findByKey($key);
    }

    private function translate(string $text): string
    {
        if (!function_exists('__')) {
            return $text;
        }

        return __(''.$text, 'ctx-events');
    }
}
