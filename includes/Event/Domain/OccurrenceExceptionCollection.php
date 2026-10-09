<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Shared\Domain\Abstract\Collection;
use InvalidArgumentException;

/**
 * @extends Collection<OccurrenceException>
 */
final readonly class OccurrenceExceptionCollection extends Collection
{
    public static function from(OccurrenceException ...$exceptions): self
    {
        $keys = array_map(static fn (OccurrenceException $exception): string => (string) $exception->occurrenceKey, $exceptions);
        if (count($keys) !== count(array_unique($keys))) {
            throw new InvalidArgumentException('An occurrence may only have one exception.');
        }

        return new self($exceptions);
    }

    public function findFor(OccurrenceKey $occurrenceKey): ?OccurrenceException
    {
        foreach ($this->items as $exception) {
            if ($exception->occurrenceKey->equals($occurrenceKey)) {
                return $exception;
            }
        }

        return null;
    }
}
