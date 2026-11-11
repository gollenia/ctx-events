<?php

declare(strict_types=1);

use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieConfiguration;
use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieWebhookRequestProcessor;
use Contexis\Events\Payment\Presentation\PaymentWebhookController;
use Mollie\Api\Webhooks\SignatureValidator;

beforeEach(function () {
    $wordpressIncludes = dirname(__DIR__, 5) . '/wp-includes';
    if (!is_file($wordpressIncludes . '/rest-api/class-wp-rest-request.php')) {
        $this->markTestSkipped('Requires a local WordPress installation.');
    }

    require_once $wordpressIncludes . '/rest-api/class-wp-rest-request.php';
});

test('accepts a signed Mollie ping with WordPress canonicalized headers', function () {
    $payload = '{"resource":"event","type":"hook.ping","id":"evt_ping"}';
    $processor = new MollieWebhookRequestProcessor(configuration: new MollieConfiguration([
        'webhook_signing_secret' => 'secret-123',
    ]));
    $controller = (new ReflectionClass(PaymentWebhookController::class))->newInstanceWithoutConstructor();
    $request = new WP_REST_Request('POST');
    $request->set_body($payload);
    $request->set_header('X-Mollie-Signature', SignatureValidator::createSignature($payload, 'secret-123'));

    $headers = (new ReflectionMethod(PaymentWebhookController::class, 'extractHeaders'))->invoke($controller, $request);
    $resolution = $processor->resolve([], $request->get_body(), $headers);

    expect($request->get_headers())->toHaveKey('x_mollie_signature')
        ->and($resolution->shouldIgnore)->toBeTrue()
        ->and($resolution->externalId)->toBeNull();
});
