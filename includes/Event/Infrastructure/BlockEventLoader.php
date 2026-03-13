<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Application\Service\EventResponseAssembler;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Platform\Bootstrap;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Infrastructure\Wordpress\UserContextFactory;

final class BlockEventLoader
{
    /** @var array<int, EventResponse|null> */
    private static array $cache = [];

    public static function load(int $postId): ?EventResponse
    {
        if (array_key_exists($postId, self::$cache)) {
            return self::$cache[$postId];
        }

        $container = Bootstrap::container();
        $event = $container->get(EventRepository::class)->find(EventId::from($postId));

        if (!$event) {
            self::$cache[$postId] = null;
            return null;
        }

        $includes = new EventIncludeRequest(
            tickets: true,
            location: true,
            person: true,
            bookings: true,
        );

        $userContext = UserContextFactory::createFromCurrentUser();
        $collection = $container
            ->get(EventResponseAssembler::class)
            ->mapEventCollection(EventCollection::from($event), $includes, $userContext);

        self::$cache[$postId] = $collection->first();

        return self::$cache[$postId];
    }

    public static function formatDateRange(\DateTimeImmutable $start, \DateTimeImmutable $end): string
    {
        $format = get_option('date_format');
        $startFormatted = wp_date($format, $start->getTimestamp());
        $endFormatted = wp_date($format, $end->getTimestamp());

        if ($startFormatted === $endFormatted) {
            return $startFormatted;
        }

        return $startFormatted . ' – ' . $endFormatted;
    }

    public static function formatTimeRange(\DateTimeImmutable $start, \DateTimeImmutable $end): string
    {
        $format = get_option('time_format');

        return wp_date($format, $start->getTimestamp()) . ' – ' . wp_date($format, $end->getTimestamp());
    }

    public static function formatPrice(Price $price): string
    {
        $formatter = new \NumberFormatter(get_locale(), \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($price->toFloat(), $price->currency->toString());
    }

    public static function renderIcon(string $name): string
    {
        static $svgCache = [];
        static $variant = null;

        $variant ??= (string) get_option(WpEventOptions::EVENT_ICON_VARIANT, 'default');

        if ($variant === 'material') {
            $html = '<i class="material-icons material-symbols-outlined">' . esc_html($name) . '</i>';
            return (string) apply_filters('ctx_events_block_icon', $html, $name);
        }

        if (!array_key_exists($name, $svgCache)) {
            $file = dirname(__FILE__, 4) . '/assets/icons/' . $name . '.svg';
            $svgCache[$name] = is_file($file) ? (string) file_get_contents($file) : '';
        }

        return (string) apply_filters('ctx_events_block_icon', $svgCache[$name], $name);
    }
}
