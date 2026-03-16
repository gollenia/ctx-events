<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Mapper;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionId;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Uri\Rfc3986\Uri;

final class TransactionMapper
{
    public static function map(array $row): Transaction
    {
        $details = self::decodeDetails($row['details'] ?? null);
        $bankData = self::mapBankData($details['bankData'] ?? null);

        return new Transaction(
            id: TransactionId::from((int) $row['id']),
            bookingId: BookingId::from((int) $row['booking_id']),
            amount: Price::from((int) $row['amount'], Currency::fromCode($row['currency'] ?? 'EUR')),
            gateway: $row['gateway'],
            status: TransactionStatus::from((int) $row['status']),
            createdAt: new \DateTimeImmutable($row['created_at']),
            externalId: $row['external_id'] ?? null,
            bankData: $bankData,
            instructions: (string) ($details['instructions'] ?? ''),
            checkoutUrl: !empty($details['checkoutUrl']) ? Uri::parse((string) $details['checkoutUrl']) : null,
            gatewayUrl: !empty($details['gatewayUrl']) ? Uri::parse((string) $details['gatewayUrl']) : null,
            expiresAt: !empty($row['expires_at']) ? new \DateTimeImmutable((string) $row['expires_at']) : null,
        );
    }

    private static function decodeDetails(mixed $details): array
    {
        if (is_array($details)) {
            return $details;
        }

        if (!is_string($details) || $details === '') {
            return [];
        }

        $decoded = json_decode($details, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function mapBankData(mixed $bankData): ?BankData
    {
        if (!is_array($bankData)) {
            return null;
        }

        $accountHolder = (string) ($bankData['accountHolder'] ?? '');
        $iban = (string) ($bankData['iban'] ?? '');
        $bic = (string) ($bankData['bic'] ?? '');
        $bankName = (string) ($bankData['bankName'] ?? '');

        if ($accountHolder === '' && $iban === '' && $bic === '' && $bankName === '') {
            return null;
        }

        return BankData::fromValues($accountHolder, $iban, $bic, $bankName);
    }
}
