<?php

declare(strict_types=1);

use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieWebhookReferenceResolver;

test('resolves legacy mollie webhook id parameter', function () {
    $resolver = new MollieWebhookReferenceResolver();

    $resolution = $resolver->resolve(['id' => 'tr_legacy_123']);

    expect($resolution->externalId)->toBe('tr_legacy_123')
        ->and($resolution->shouldIgnore)->toBeFalse();
});

test('resolves next gen mollie payment event by entity id', function () {
    $resolver = new MollieWebhookReferenceResolver();

    $resolution = $resolver->resolve([], [
        'resource' => 'event',
        'type' => 'payment.paid',
        'entityId' => 'tr_new_123',
    ]);

    expect($resolution->externalId)->toBe('tr_new_123')
        ->and($resolution->shouldIgnore)->toBeFalse();
});

test('resolves next gen mollie payment event by embedded payment entity', function () {
    $resolver = new MollieWebhookReferenceResolver();

    $resolution = $resolver->resolve([], [
        'resource' => 'event',
        'type' => 'payment.failed',
        '_embedded' => [
            'entity' => [
                'resource' => 'payment',
                'id' => 'tr_snapshot_123',
            ],
        ],
    ]);

    expect($resolution->externalId)->toBe('tr_snapshot_123')
        ->and($resolution->shouldIgnore)->toBeFalse();
});

test('ignores non payment mollie events', function () {
    $resolver = new MollieWebhookReferenceResolver();

    $resolution = $resolver->resolve([], [
        'resource' => 'event',
        'type' => 'refund.created',
        'entityId' => 're_123',
    ]);

    expect($resolution->externalId)->toBeNull()
        ->and($resolution->shouldIgnore)->toBeTrue();
});

test('marks invalid payloads without payment reference as invalid', function () {
    $resolver = new MollieWebhookReferenceResolver();

    $resolution = $resolver->resolve([], ['resource' => 'event']);

    expect($resolution->externalId)->toBeNull()
        ->and($resolution->shouldIgnore)->toBeFalse();
});
