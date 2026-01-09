<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation;

final class Debugger
{
    public static function log(string $message): void
    {
        error_log($message);
    }

	public static function logToFile(string $message): void
    {
        file_put_contents(ABSPATH . 'debug.log', $message . PHP_EOL, FILE_APPEND);
    }

	public static function dump(mixed $data): void
    {
        error_log(var_export($data, true));
    }
}