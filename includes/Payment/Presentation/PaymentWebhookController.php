<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation;

use Contexis\Events\Payment\Application\UseCases\SyncTransactionStatus;
use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieWebhookRequestProcessor;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class PaymentWebhookController implements RestController
{
    public function __construct(
        private SyncTransactionStatus $syncTransactionStatus,
        private ?MollieWebhookRequestProcessor $mollieWebhookRequestProcessor = null,
    ) {
        $this->mollieWebhookRequestProcessor ??= new MollieWebhookRequestProcessor();
    }

    public function register(): void
    {
        register_rest_route('events/v3', '/payments/webhooks/(?P<gateway>[A-Za-z0-9_-]+)', [[
            'methods' => 'POST',
            'callback' => [$this, 'handleGatewayWebhook'],
            'permission_callback' => '__return_true',
        ]]);
    }

    public function handleGatewayWebhook(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->handleWebhookRequest((string) $request->get_param('gateway'), $request);
    }

    private function handleWebhookRequest(string $gateway, \WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $resolution = match ($gateway) {
                'mollie' => $this->mollieWebhookRequestProcessor->resolve(
                    $request->get_params(),
                    $this->extractRawBody($request),
                    $this->extractHeaders($request),
                ),
                default => null,
            };

            if ($resolution === null) {
                return new \WP_REST_Response(['message' => "Unsupported payment webhook gateway: {$gateway}"], 404);
            }

            if ($resolution->shouldIgnore) {
                return new \WP_REST_Response(['ignored' => true], 202);
            }

            if ($resolution->externalId === null) {
                return new \WP_REST_Response(['message' => 'No payment reference found in webhook payload.'], 400);
            }

            $this->syncTransactionStatus->execute($resolution->externalId);

            return new \WP_REST_Response(null, 204);
        } catch (\DomainException $exception) {
            $status = $exception->getCode();
            if (!is_int($status) || $status < 400 || $status > 499) {
                $status = 422;
            }

            return new \WP_REST_Response(['message' => $exception->getMessage()], $status);
        }
    }

    /**
     * @return array<string, string>
     */
    private function extractHeaders(\WP_REST_Request $request): array
    {
        $headers = [];

        foreach ($request->get_headers() as $name => $values) {
            if (is_array($values)) {
                $headers[strtolower($name)] = implode(', ', array_map('strval', $values));
                continue;
            }

            if (is_string($values)) {
                $headers[strtolower($name)] = $values;
            }
        }

        return $headers;
    }

    private function extractRawBody(\WP_REST_Request $request): string
    {
        $body = $request->get_body();

        return is_string($body) ? $body : '';
    }
}
