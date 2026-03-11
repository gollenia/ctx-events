<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\Contracts\ReferenceGenerator;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;

final readonly class BookingReferenceGenerator implements ReferenceGenerator
{
    private const int UUID_LENGTH = 6;
    private const int MAX_ATTEMPTS = 10;
    private const string ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public function __construct(private Database $database)
    {
    }


    public function create(string $prefix = '', string $suffix = ''): BookingReference
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
			$code = $this->generateCandidate();
            $reference = BookingReference::fromParts($code, $prefix, $suffix);

            if ($this->exists($reference->toString())) {
                continue;
            }

            return $reference;
        }

        throw new \RuntimeException('Unable to generate a unique booking reference after multiple attempts.');
    }

    private function generateCandidate(): string
    {
        $alphabet = self::ALPHABET;
        $lastAlphabetIndex = strlen($alphabet) - 1;
        $characters = [];

        for ($index = 0; $index < self::UUID_LENGTH; $index++) {
            $characters[] = $alphabet[random_int(0, $lastAlphabetIndex)];
        }

        return implode('', $characters);
    }

    private function exists(string $uuid): bool
    {
        $table = BookingMigration::getTableName();
        $sql = $this->database->prepare("SELECT 1 FROM {$table} WHERE uuid = %s LIMIT 1", $uuid);
        return $this->database->getVar($sql) !== null;
    }
}
