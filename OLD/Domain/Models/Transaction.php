<?php
namespace Contexis\Events\Domain\Models;
use DateTime;
use Contexis\Events\Repositories\TransactionRepository;

class Transaction {
	public int $id = 0;
	public ?DateTime $created_at = null;
	public ?DateTime $expires_at = null;
	public int $booking_id = 0;
	public string $external_id = '';
	public float $amount;
	public string $gateway;
	public string $status;
	public string $checkout_url = '';

	public function to_array() : array
	{
		return [
			'id' => $this->id,
			'created_at' => $this->created_at->format('c'),
			'expires_at' => $this->expires_at?->format('c'),
			'booking_id' => $this->booking_id,
			'external_id' => $this->external_id,
			'amount' => $this->amount,
			'gateway' => $this->gateway,
			'status' => $this->status,
			'checkout_url' => $this->checkout_url
		];
	}

	public function get_status_label(): string
	{
		return match ($this->status) {
			'pending' => __('Pending', 'events'),
			'open' => __('Open', 'events'),
			'authorized' => __('Authorized', 'events'),
			'paid' => __('Completed', 'events'),
			'failed' => __('Failed', 'events'),
			'canceled' => __('Canceled', 'events'),
			'expired' => __('Expired', 'events'),
			'refunded' => __('Refunded', 'events'),
			'chargeback' => __('Chargeback', 'events'),
			default => __('Unknown', 'events'),
		};
	}

	public function save(): bool
	{
		if ($this->id !== 0) {
			return TransactionRepository::update($this);
		}
		$this->id = TransactionRepository::create($this);
		return $this->id !== 0;
	}

	public function set_status(string $status): void
	{
		$this->status = $status;
	}
}