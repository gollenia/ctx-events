<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\Contracts\ReferenceGeneratorContract;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;

final readonly class BookingReferenceGenerator implements ReferenceGeneratorContract
{
    private const int UUID_LENGTH = 12;
    private const int MAX_ATTEMPTS = 10;
    private const string ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct(private Database $database)
    {
    }


    public function create(): BookingReference
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $uuid = BookingReference::fromString($this->generateCandidate());

            if ($this->exists($uuid->toString())) {
                continue;
            }

            return $uuid;
        }

        throw new \RuntimeException('Unable to generate a unique booking UUID after multiple attempts.');
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
