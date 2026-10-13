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

test('accepts signed next gen Mollie payment snapshot payloads', function () {
    $payload = json_encode([
        'resource' => 'payment',
        'id' => 'tr_snapshot_123',
        'status' => 'paid',
    ], JSON_THROW_ON_ERROR);

    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    $resolution = $processor->resolve([], $payload, [
        'x-mollie-signature' => SignatureValidator::createSignature($payload, 'secret-123'),
    ]);

    expect($resolution->externalId)->toBe('tr_snapshot_123')
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

test('rejects next gen mollie payloads when no signing secret is configured', function () {
    $payload = json_encode([
        'resource' => 'payment',
        'id' => 'tr_snapshot_123',
    ], JSON_THROW_ON_ERROR);

    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration(''),
    );

    expect(fn() => $processor->resolve([], $payload, []))
        ->toThrow(DomainException::class, 'Mollie webhook signing secret is not configured.');
});

test('does not let a parsed JSON payload bypass signature verification', function () {
    $payload = json_encode([
        'resource' => 'payment',
        'id' => 'tr_snapshot_123',
    ], JSON_THROW_ON_ERROR);

    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    expect(fn() => $processor->resolve(['id' => 'tr_snapshot_123'], $payload, []))
        ->toThrow(DomainException::class, 'Missing Mollie webhook signature.');
});

test('rejects empty mollie webhook requests', function () {
    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    $resolution = $processor->resolve([], '', []);

    expect($resolution->externalId)->toBeNull()
        ->and($resolution->shouldIgnore)->toBeFalse();
});

test('accepts Mollie classic payment webhook posts', function () {
    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    $resolution = $processor->resolve(['id' => 'tr_Classic123'], '', []);

    expect($resolution->externalId)->toBe('tr_Classic123')
        ->and($resolution->shouldIgnore)->toBeFalse();
});

test('does not accept arbitrary values as Mollie classic payment ids', function () {
    $processor = new MollieWebhookRequestProcessor(
        new MollieWebhookReferenceResolver(),
        configuredMollieWebhookConfiguration('secret-123'),
    );

    $resolution = $processor->resolve(['id' => 'not-a-mollie-payment'], '', []);

    expect($resolution->externalId)->toBeNull()
        ->and($resolution->shouldIgnore)->toBeFalse();
});

function configuredMollieWebhookConfiguration(string $secret): MollieConfiguration
{
    return new MollieConfiguration([
        'webhook_signing_secret' => $secret,
    ]);
}
