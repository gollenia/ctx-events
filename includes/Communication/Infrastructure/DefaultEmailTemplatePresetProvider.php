<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailTemplatePresetProvider;
use Contexis\Events\Communication\Domain\EmailTemplatePreset;
use Contexis\Events\Communication\Domain\EmailTemplatePresetCollection;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;

if (!function_exists(__NAMESPACE__ . '\\__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return function_exists('__') ? \__($text, $domain) : $text;
    }
}

final readonly class DefaultEmailTemplatePresetProvider implements EmailTemplatePresetProvider
{
    public function all(): EmailTemplatePresetCollection
    {
        return EmailTemplatePresetCollection::from(
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_PENDING_MANUAL,
                label: __('Booking pending manual', 'ctx-events'),
                description: __('Sent to participants when a booking was received but still requires manual confirmation or offline payment.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_PENDING_MANUAL,
                target: EmailTarget::CUSTOMER,
                subject: __('We received your booking', 'ctx-events'),
                body: __('Your booking has been received and is currently awaiting manual confirmation.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CREATED_ONLINE,
                label: __('Booking created online', 'ctx-events'),
                description: __('Sent to participants when an online payment booking has been started.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_CREATED_ONLINE,
                target: EmailTarget::CUSTOMER,
                subject: __('Your booking was created', 'ctx-events'),
                body: __('Your booking was created and the online payment process has started.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CONFIRMED_MANUAL,
                label: __('Booking confirmed manually', 'ctx-events'),
                description: __('Sent to participants when a booking was manually confirmed.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_CONFIRMED_MANUAL,
                target: EmailTarget::CUSTOMER,
                subject: __('Your booking is confirmed', 'ctx-events'),
                body: __('Your booking has been confirmed. We look forward to seeing you.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CONFIRMED_ONLINE,
                label: __('Booking confirmed online', 'ctx-events'),
                description: __('Sent to participants when an online payment completed successfully.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_CONFIRMED_ONLINE,
                target: EmailTarget::CUSTOMER,
                subject: __('Your booking is confirmed', 'ctx-events'),
                body: __('Your online payment was successful and your booking is now confirmed.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_OFFLINE_EXPIRING,
                label: __('Offline payment expiring', 'ctx-events'),
                description: __('Reminder that an offline payment must be completed soon.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_OFFLINE_EXPIRING,
                target: EmailTarget::CUSTOMER,
                subject: __('Your booking is about to expire', 'ctx-events'),
                body: __('Please complete your payment soon or the reserved spaces may be released.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_OFFLINE_EXPIRED,
                label: __('Offline payment expired', 'ctx-events'),
                description: __('Sent when an unpaid offline booking expired and the spaces were released.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_OFFLINE_EXPIRED,
                target: EmailTarget::CUSTOMER,
                subject: __('Your booking has expired', 'ctx-events'),
                body: __('Your booking expired because the offline payment was not completed in time.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_PAYMENT_FAILED,
                label: __('Payment failed', 'ctx-events'),
                description: __('Sent to participants when an online payment failed.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_PAYMENT_FAILED,
                target: EmailTarget::CUSTOMER,
                subject: __('Your payment failed', 'ctx-events'),
                body: __('Unfortunately, your online payment could not be completed.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_DENIED,
                label: __('Booking denied', 'ctx-events'),
                description: __('Sent to participants when a booking was denied.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_DENIED,
                target: EmailTarget::CUSTOMER,
                subject: __('Your booking was denied', 'ctx-events'),
                body: __('Unfortunately, your booking could not be accepted.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::BOOKING_CANCELLED,
                label: __('Booking cancelled', 'ctx-events'),
                description: __('Sent to participants when a booking was cancelled.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_CANCELLED,
                target: EmailTarget::CUSTOMER,
                subject: __('Your booking was cancelled', 'ctx-events'),
                body: __('Your booking has been cancelled.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::TICKET_CANCELLED,
                label: __('Ticket cancelled', 'ctx-events'),
                description: __('Sent to participants when a single attendee ticket in a booking was cancelled.', 'ctx-events'),
                trigger: EmailTrigger::TICKET_CANCELLED,
                target: EmailTarget::CUSTOMER,
                subject: __('A ticket in your booking was cancelled', 'ctx-events'),
                body: __('A ticket in your booking has been cancelled.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL,
                label: __('Admin: booking pending manual', 'ctx-events'),
                description: __('Sent to admins when a new booking needs manual handling.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_PENDING_MANUAL,
                target: EmailTarget::ADMIN,
                subject: __('A booking needs manual confirmation', 'ctx-events'),
                body: __('A new booking was received and requires manual confirmation or offline payment handling.', 'ctx-events'),
            ),
            EmailTemplatePreset::create(
                key: EmailTemplateKey::ADMIN_BOOKING_CREATED_ONLINE,
                label: __('Admin: booking created online', 'ctx-events'),
                description: __('Sent to admins when a new online booking was created.', 'ctx-events'),
                trigger: EmailTrigger::BOOKING_CREATED_ONLINE,
                target: EmailTarget::ADMIN,
                subject: __('A new online booking was created', 'ctx-events'),
                body: __('A new booking with online payment was created.', 'ctx-events'),
            ),
        );
    }

    public function find(EmailTemplateKey $key): ?EmailTemplatePreset
    {
        return $this->all()->findByKey($key);
    }

}
