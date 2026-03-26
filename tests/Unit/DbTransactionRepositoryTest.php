<?php
declare(strict_types=1);

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Infrastructure\DbTransactionRepository;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

if (!function_exists('wp_json_encode')) {
	function wp_json_encode(mixed $value): string|false
	{
		return json_encode($value);
	}
}

test('saving an offline transaction skips external id lookup and inserts null external id', function () {
	global $wpdb;
	$wpdb = (object) ['prefix' => 'wp_'];

	$db = Mockery::mock(Database::class);
	$repository = new DbTransactionRepository($db);
	$bookingId = BookingId::from(42);
	$transaction = new Transaction(
		id: null,
		bookingId: $bookingId,
		amount: Price::from(5000, Currency::fromCode('EUR')),
		gateway: 'offline',
		status: TransactionStatus::PENDING,
		createdAt: new DateTimeImmutable('2026-03-25 12:00:00'),
	);

	$db->shouldReceive('insert')
		->once()
		->with(
			'wp_ctx_event_transactions',
			Mockery::on(
				static fn(array $data): bool =>
					array_key_exists('external_id', $data) &&
					$data['external_id'] === null,
			),
		)
		->andReturn(1);

	$repository->save($transaction);
});

test('finding by empty external id returns null without querying the database', function () {
	global $wpdb;
	$wpdb = (object) ['prefix' => 'wp_'];

	$db = Mockery::mock(Database::class);
	$db->shouldNotReceive('prepare');
	$db->shouldNotReceive('getRow');

	$repository = new DbTransactionRepository($db);

	expect($repository->findByExternalId(''))->toBeNull();
});
