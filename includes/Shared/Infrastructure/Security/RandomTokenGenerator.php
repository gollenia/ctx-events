<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Security;

use Contexis\Events\Shared\Domain\Contracts\TokenGenerator;

final readonly class RandomTokenGenerator implements TokenGenerator
{
    public function generate(int $bytes = 32): string
    {
        if ($bytes <= 0) {
            throw new \InvalidArgumentException('Token bytes must be greater than zero.');
        }

        $raw = random_bytes($bytes);
        $base64 = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

        return $base64;
    }
}
