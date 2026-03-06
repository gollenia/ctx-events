<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Security;

use Contexis\Events\Shared\Domain\Contracts\HashGenerator;

final readonly class WpHashGenerator implements HashGenerator
{
    public function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, (string) \wp_salt('auth'));
    }

    public function verify(string $payload, string $signature): bool
    {
        if ($signature === '') {
            return false;
        }

        return hash_equals($this->sign($payload), $signature);
    }
}
