<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Mollie;

final class MollieWebhookReferenceResolver
{
    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $payload
     */
    public function resolve(array $params, array $payload = []): MollieWebhookReferenceResolution
    {
        if ($payload === []) {
            return MollieWebhookReferenceResolution::invalid();
        }

        $eventType = $this->readString($payload, 'type');
        if ($eventType !== '' && !str_starts_with($eventType, 'payment.')) {
            return MollieWebhookReferenceResolution::ignore();
        }

        $embeddedEntity = $payload['_embedded']['entity'] ?? null;
        if (is_array($embeddedEntity)) {
            $embeddedResource = $this->readString($embeddedEntity, 'resource');
            $embeddedId = $this->readString($embeddedEntity, 'id');

            if ($embeddedResource === 'payment' && $embeddedId !== '') {
                return MollieWebhookReferenceResolution::sync($embeddedId);
            }
        }

        $entityId = $this->readString($payload, 'entityId');
        if ($entityId !== '' && ($eventType === '' || str_starts_with($entityId, 'tr_'))) {
            return MollieWebhookReferenceResolution::sync($entityId);
        }

        return MollieWebhookReferenceResolution::invalid();
    }

    /**
     * @param array<string, mixed> $source
     */
    private function readString(array $source, string $key): string
    {
        $value = $source[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }
}
