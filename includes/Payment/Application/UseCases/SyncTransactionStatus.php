<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Services\SyncBookingFromTransaction;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final class SyncTransactionStatus
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private GatewayRepository $gatewayRepository,
        private SyncBookingFromTransaction $syncBookingFromTransaction,
        private CurrentActorProvider $currentActorProvider,
    ) {
    }

    public function execute(string $externalId, ?Actor $actor = null): void
    {
        $transaction = $this->transactionRepository->findByExternalId($externalId);

        if ($transaction === null) {
            throw new \DomainException("Transaction not found: {$externalId}");
        }

        $gateway = $this->gatewayRepository->find($transaction->gateway);

        if ($gateway === null) {
            throw new \DomainException("Payment gateway not found: {$transaction->gateway}");
        }

        $verifiedTransaction = $gateway->verifyPayment($transaction);
        $this->transactionRepository->save($verifiedTransaction);

        $resolvedActor = $actor ?? $this->resolveActor($transaction->gateway);
        $this->syncBookingFromTransaction->execute($verifiedTransaction, $resolvedActor);
    }

    private function resolveActor(string $gateway): Actor
    {
        $currentActor = $this->currentActorProvider->current();

        if ($currentActor->id !== 0 || $currentActor->name !== '') {
            return $currentActor;
        }

        return Actor::gateway($gateway);
    }
}
