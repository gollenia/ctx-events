<?php

declare(strict_types=1);

use Contexis\Events\Payment\Domain\Enums\GatewayStatus;
use Contexis\Events\Payment\Application\Dtos\GatewayListItemDto;
use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieConfiguration;
use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieGateway;
use Tests\Support\FakePaymentGateway;

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

test('reports a missing Mollie configuration before its operating mode', function () {
    $gateway = new MollieGateway(new MollieConfiguration([
        'mode' => 'test',
    ]));

    expect($gateway->getStatus())->toBe(GatewayStatus::MissingConfiguration);
});

test('reports a valid Mollie test configuration as test mode', function () {
    $gateway = new MollieGateway(new MollieConfiguration([
        'mode' => 'test',
        'api_key_test' => 'test_key',
        'webhook_signing_secret' => 'secret-123',
    ]));

    expect($gateway->getStatus())->toBe(GatewayStatus::TestMode);
});

test('reports valid live Mollie gateways as active or inactive', function () {
    $activeGateway = new MollieGateway(new MollieConfiguration([
        'enabled' => true,
        'mode' => 'live',
        'api_key_live' => 'live_key',
        'webhook_signing_secret' => 'secret-123',
    ]));
    $inactiveGateway = new MollieGateway(new MollieConfiguration([
        'enabled' => false,
        'mode' => 'live',
        'api_key_live' => 'live_key',
        'webhook_signing_secret' => 'secret-123',
    ]));

    expect($activeGateway->getStatus())->toBe(GatewayStatus::Active)
        ->and($inactiveGateway->getStatus())->toBe(GatewayStatus::Inactive);
});

test('exposes the gateway status as a stable API value', function () {
    $item = GatewayListItemDto::fromPaymentGateway(
        new FakePaymentGateway(valid: false),
    );

    expect($item->toArray()['status'])->toBe('missing_configuration');
});
