<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure\Wordpress;

use Contexis\Events\Booking\Application\UseCases\ResolveBookingPaymentLink;
use Contexis\Events\Booking\Presentation\BookingPaymentLink;
use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class BookingPaymentLinkRedirect implements Registrar
{
    public function __construct(
        private readonly ResolveBookingPaymentLink $resolveBookingPaymentLink,
    ) {
    }

    public function hook(): void
    {
        add_action('template_redirect', [$this, 'handleRedirect']);
    }

    public function handleRedirect(): void
    {
        $reference = isset($_GET[BookingPaymentLink::QUERY_VAR])
            ? sanitize_text_field((string) $_GET[BookingPaymentLink::QUERY_VAR])
            : null;

        if (!is_string($reference) || trim($reference) === '') {
            return;
        }

        try {
            $transaction = $this->resolveBookingPaymentLink->execute($reference);

            if ($transaction->checkoutUrl === null) {
                throw new \DomainException('No checkout URL is available for this booking.');
            }

            wp_redirect($transaction->checkoutUrl->toString(), 302);
            exit;
        } catch (\DomainException|\RuntimeException $exception) {
            status_header(422);
            wp_die(
                esc_html($exception->getMessage()),
                esc_html__('Payment link unavailable', 'ctx-events'),
                ['response' => 422],
            );
        }
    }
}
