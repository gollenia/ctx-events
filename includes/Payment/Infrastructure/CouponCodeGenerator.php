<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

final class CouponCodeGenerator
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const CODE_LENGTH = 10;

    public function generate(): string
    {
        $code = '';
        $alphabetLength = strlen(self::ALPHABET) - 1;

        for ($index = 0; $index < self::CODE_LENGTH; $index++) {
            $code .= self::ALPHABET[random_int(0, $alphabetLength)];
        }

        return $code;
    }
}
