<?php

namespace Contexis\Events\Models;
use DateTimeImmutable;

enum TransactionType: string { case Sale='sale'; case Refund='refund'; }


final readonly class Transaction
{
	public function __construct(
		public TransactionType $type = TransactionType::Sale,
		public DateTimeImmutable $date = new DateTimeImmutable('now'),
		public float $amount,
		public string $gateway,
		public string $id = '',
		public string $notes = ''
	) {
		 if ($amount <= 0) throw new \InvalidArgumentException('amount > 0 required');
	}

	public function to_array() : array
	{
		return [
			'type' => $this->type->value,
			'date' => $this->date->format('c'),
			'amount' => $this->amount,
			'gateway' => $this->gateway,
			'id' => $this->id,
			'notes' => $this->notes,
		];
	}

	public static function fromArray(array $data): self
    {
        $type    = TransactionType::from($data['type'] ?? TransactionType::Sale->value);
        $amount  = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $gateway = $data['gateway'] ?? '';

        if ($amount === false || $amount <= 0) {
            throw new \InvalidArgumentException('Invalid or missing amount');
        }
        if (!is_string($gateway) || $gateway === '') {
            throw new \InvalidArgumentException('Invalid or missing gateway');
        }

        return new self(
            type: $type,
            date: isset($data['date']) ? new DateTimeImmutable($data['date']) : new DateTimeImmutable('now'),
            amount: (float) $amount,
            gateway: $gateway,
            id: (string)($data['id'] ?? ''),
            notes: (string)($data['notes'] ?? '')
        );
    }
}