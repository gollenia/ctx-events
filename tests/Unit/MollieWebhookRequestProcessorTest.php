<?php

declare(strict_types=1);

use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieConfiguration;
use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieWebhookRequestProcessor;
use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieWebhookReferenceResolver;
use Mollie\Api\Webhooks\SignatureValidator;

test('accepts signed next gen mollie payment webhook payloads', function () {
    $payload = json_encode([
        'resource' => 'event',
        'id' => 'evt_123',
        'type' => 'payment.paid',
        'entityId' => 'tr_signed_123',
        'createdAt' => '2026-03-18T12:00:00+00:00',
    ], JSON_THROW_ON_ERROR);

    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    $resolution = $processor->resolve([], $payload, [
        'x-mollie-signature' => SignatureValidator::createSignature($payload, 'secret-123'),
    ]);

    expect($resolution->externalId)->toBe('tr_signed_123')
        ->and($resolution->shouldIgnore)->toBeFalse();
});

test('rejects unsigned next gen mollie payloads when a signing secret is configured', function () {
    $payload = json_encode([
        'resource' => 'event',
        'id' => 'evt_123',
        'type' => 'payment.paid',
        'entityId' => 'tr_signed_123',
        'createdAt' => '2026-03-18T12:00:00+00:00',
    ], JSON_THROW_ON_ERROR);

    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    expect(fn() => $processor->resolve([], $payload, []))
        ->toThrow(DomainException::class, 'Missing Mollie webhook signature.');
});

test('still accepts legacy mollie webhook requests without signatures', function () {
    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    $resolution = $processor->resolve(['id' => 'tr_legacy_456'], '', []);

    expect($resolution->externalId)->toBe('tr_legacy_456')
        ->and($resolution->shouldIgnore)->toBeFalse();
});

function configuredMollieWebhookConfiguration(string $secret): MollieConfiguration
{
    return new MollieConfiguration([
        'webhook_signing_secret' => $secret,
    ]);
}
