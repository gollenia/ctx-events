<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Domain\GatewayCollection;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Infrastructure\Enums\PaymentProvider;

final class WpGatewayRepository implements GatewayRepository
{
    /** @var array<string, PaymentGateway> */
    private array $instances = [];

    public function find(string $id): ?PaymentGateway
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        foreach (PaymentProvider::cases() as $provider) {
            $className = $provider->getGatewayClass();
            $gateway = new $className();
            $this->instances[$provider->value] = $gateway;
        }

        return $this->instances[$id] ?? null;
    }

    public function findAll(): GatewayCollection
    {
        $allIds = array_map(fn($case) => $case->value, PaymentProvider::cases());
        $result = [];
        foreach ($allIds as $id) {
            try {
                $result[] = $this->find($id);
            } catch (\Throwable $e) {
                error_log($e->getMessage());
            }
        }
        return GatewayCollection::from(...$result);
    }

    public function findActive(): GatewayCollection
    {
        return GatewayCollection::from(...array_filter(
            $this->findAll()->toArray(),
            fn(PaymentGateway $g) => $g->isEnabled()
        ));
    }
}
