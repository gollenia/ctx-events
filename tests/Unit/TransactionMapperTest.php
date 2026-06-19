<?php
declare(strict_types=1);

use Contexis\Events\Payment\Infrastructure\Mapper\TransactionMapper;

test('transaction mapper prefers transaction_date for the domain createdAt value', function () {
    $transaction = TransactionMapper::map([
        'id' => 5,
        'booking_id' => 11,
        'amount' => 5000,
        'currency' => 'EUR',
        'gateway' => 'offline',
        'status' => 0,
        'transaction_date' => '2026-06-10 08:30:00',
        'created_at' => '2026-06-15 12:45:00',
        'external_id' => null,
        'details' => null,
        'expires_at' => '2026-06-12 08:30:00',
    ]);

    expect($transaction->createdAt->format('Y-m-d H:i:s'))->toBe('2026-06-10 08:30:00');
});

test('transaction mapper falls back to created_at when transaction_date is missing', function () {
    $transaction = TransactionMapper::map([
        'id' => 6,
        'booking_id' => 12,
        'amount' => 5000,
        'currency' => 'EUR',
        'gateway' => 'offline',
        'status' => 0,
        'created_at' => '2026-06-15 12:45:00',
        'external_id' => null,
        'details' => null,
        'expires_at' => null,
    ]);

    expect($transaction->createdAt->format('Y-m-d H:i:s'))->toBe('2026-06-15 12:45:00');
});
