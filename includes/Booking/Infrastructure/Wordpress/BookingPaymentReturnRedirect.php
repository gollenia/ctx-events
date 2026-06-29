<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure\Wordpress;

use Contexis\Events\Booking\Presentation\BookingReturnLink;
use Contexis\Events\Payment\Application\UseCases\ProcessBookingPaymentReturn;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class BookingPaymentReturnRedirect implements Registrar
{
    public function __construct(
        private readonly ProcessBookingPaymentReturn $processBookingPaymentReturn,
    ) {
    }

    public function hook(): void
    {
        add_action('template_redirect', [$this, 'handleRedirect']);
    }

    public function handleRedirect(): void
    {
        $reference = isset($_GET[BookingReturnLink::QUERY_VAR])
            ? sanitize_text_field((string) $_GET[BookingReturnLink::QUERY_VAR])
            : null;

        if (!is_string($reference) || trim($reference) === '') {
            return;
        }

        try {
            $result = $this->processBookingPaymentReturn->execute($reference);
            $booking = $result['booking'];
            $transaction = $result['transaction'];

            $targetUrl = get_permalink($booking->eventId->toInt());
            if (!is_string($targetUrl) || $targetUrl === '') {
                $targetUrl = home_url('/');
            }

            $status = $transaction?->status->name ?? 'UNKNOWN';
            $redirectUrl = add_query_arg([
                'ctx_events_booking_reference' => $booking->reference->toString(),
                'ctx_events_payment_status' => strtolower($status),
            ], $targetUrl);

            wp_safe_redirect($redirectUrl, 302);
            exit;
        } catch (\DomainException|\RuntimeException $exception) {
            status_header(422);
            wp_die(
                esc_html($exception->getMessage()),
                esc_html__('Payment return failed', 'ctx-events'),
                ['response' => 422],
            );
        }
    }
}
