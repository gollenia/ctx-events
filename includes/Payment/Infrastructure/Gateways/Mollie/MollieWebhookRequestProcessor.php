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

        if ($payload === []) {
            return MollieWebhookReferenceResolution::invalid();
        }

        $this->assertValidSignature($rawBody, $headers);

        return $this->referenceResolver->resolve($params, $payload);
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
            return;
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
