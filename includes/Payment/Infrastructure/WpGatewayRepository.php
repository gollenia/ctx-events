<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Infrastructure\Enums\PaymentProvider;
use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieGateway;
use Contexis\Events\Payment\Infrastructure\Gateways\Offline\OfflineGateway;

final class WpGatewayRepository implements GatewayRepository
{
    private array $instances = [];

    public function get(string $id): PaymentGateway
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
		
		foreach (PaymentProvider::cases() as $provider) {
			$className = $provider->getGatewayClass();
			$gateway = new $className();
			$this->instances[$provider->value] = $gateway;
    	}

        return $this->instances[$id];
    }

    public function findAll(): array
    {
        $allIds = array_map(fn($case) => $case->value, PaymentProvider::cases());
        $result = [];
        foreach ($allIds as $id) {
            try {
                $result[] = $this->get($id);
            } catch (\Throwable $e) {
                error_log($e->getMessage());
            }
        }
        return $result;
    }

    public function findActive(): array
    {
        return array_filter(
            $this->findAll(),
            fn(PaymentGateway $g) => $g->isEnabled()
        );
    }
}
