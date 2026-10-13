<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Mollie;

use Mollie\Api\Exceptions\InvalidSignatureException;
use Mollie\Api\Webhooks\SignatureValidator;

final class MollieWebhookRequestProcessor
{
    public function __construct(
        private ?MollieWebhookReferenceResolver $referenceResolver = null,
        private ?MollieConfiguration $configuration = null,
    ) {
        $this->referenceResolver ??= new MollieWebhookReferenceResolver();
        $this->configuration ??= new MollieConfiguration();
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $headers
     */
    public function resolve(array $params, string $rawBody = '', array $headers = []): MollieWebhookReferenceResolution
    {
        $payload = $this->decodePayload($rawBody);
        if ($payload !== []) {
            $this->assertValidSignature($rawBody, $headers);

            return $this->referenceResolver->resolve($params, $payload);
        }

        // Payments created with webhookUrl use Mollie's classic webhook: a
        // form-encoded POST containing only `id=tr_…`. It has no signature;
        // the ID is deliberately treated only as a lookup key and the current
        // state is always fetched from Mollie before changing a booking.
        $classicPaymentId = $this->readClassicPaymentId($params);
        if ($classicPaymentId !== null) {
            return MollieWebhookReferenceResolution::sync($classicPaymentId);
        }

        return MollieWebhookReferenceResolution::invalid();
    }

    /**
     * @param array<string, mixed> $params
     */
    private function readClassicPaymentId(array $params): ?string
    {
        $paymentId = $params['id'] ?? null;
        if (!is_string($paymentId)) {
            return null;
        }

        $paymentId = trim($paymentId);

        return preg_match('/^tr_[A-Za-z0-9]+$/', $paymentId) === 1
            ? $paymentId
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(string $rawBody): array
    {
        if (trim($rawBody) === '') {
            return [];
        }

        $decoded = json_decode($rawBody, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, string> $headers
     */
    private function assertValidSignature(string $rawBody, array $headers): void
    {
        $secret = trim($this->configuration->webhookSigningSecret);
        if ($secret === '') {
            throw new \DomainException('Mollie webhook signing secret is not configured.', 401);
        }

        $signature = trim($headers['x-mollie-signature'] ?? '');
        if ($signature === '') {
            throw new \DomainException('Missing Mollie webhook signature.', 401);
        }

        try {
            SignatureValidator::validate($rawBody, $secret, $signature);
        } catch (InvalidSignatureException $exception) {
            throw new \DomainException('Invalid Mollie webhook signature.', 401, $exception);
        }
    }
}
