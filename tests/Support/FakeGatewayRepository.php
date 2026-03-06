<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Payment\Domain\GatewayCollection;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\PaymentGateway;

final class FakeGatewayRepository implements GatewayRepository
{
    /** @var PaymentGateway[] */
    private array $gateways = [];

    public static function empty(): self
    {
        return new self();
    }

    public static function withActiveGateway(): self
    {
        return self::withGateways([new FakePaymentGateway()]);
    }

    /** @param PaymentGateway[] $gateways */
    public static function withGateways(array $gateways): self
    {
        $repository = new self();
        $repository->gateways = $gateways;
        return $repository;
    }

    public function find(string $id): ?PaymentGateway
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->getId() === $id) {
                return $gateway;
            }
        }
        return null;
    }

    public function findAll(): GatewayCollection
    {
        return new GatewayCollection(...$this->gateways);
    }

    public function findActive(): GatewayCollection
    {
        return new GatewayCollection(...array_filter(
            $this->gateways,
            static fn(PaymentGateway $gateway): bool => $gateway->isEnabled()
        ));
    }
}
