<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

final class CalendarTiming
{
	/** @var array<string, float> */
	private static array $durations = [];

	public static function reset(): void
	{
		self::$durations = [];
	}

	public static function measure(string $name, callable $callback): mixed
	{
		$startedAt = microtime(true);
		try {
			return $callback();
		} finally {
			self::$durations[$name] = (self::$durations[$name] ?? 0) + (microtime(true) - $startedAt) * 1000;
		}
	}

	/** @return array<string, float> */
	public static function durations(): array
	{
		return self::$durations;
	}
}
