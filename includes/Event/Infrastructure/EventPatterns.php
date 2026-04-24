<?php

namespace Contexis\Events\Event\Infrastructure;
use Contexis\Events\Platform\Wordpress\PluginInfo;

final class EventPatterns
{
	private const PATTERN_DIR = '/patterns';
	public static function register(): void
	{
        register_block_pattern_category('ctx-events', [
            'label' => __('Events', 'ctx-events'),
        ]);

        register_block_pattern(
            'ctx-events/featured-event-split',
            [
                'title' => __('Featured Event: Image Left, Details Right', 'ctx-events'),
                'description' => __('Two-column featured event layout with the image on the left and event details on the right.', 'ctx-events'),
                'categories' => ['ctx-events'],
                'viewportWidth' => 1440,
                'content' => self::loadPatternFile('featured-event-split.html'),
            ]
        );

        register_block_pattern(
            'ctx-events/featured-event-stacked',
            [
                'title' => __('Featured Event: Stacked', 'ctx-events'),
                'description' => __('Stacked featured event layout with image first and the event content underneath.', 'ctx-events'),
                'categories' => ['ctx-events'],
                'viewportWidth' => 960,
                'content' => self::loadPatternFile('featured-event-stacked.html'),
            ]
        );

        register_block_pattern(
            'ctx-events/event-details',
            [
                'title' => __('Event Details', 'ctx-events'),
                'description' => __('Default event details block with the standard set of event metadata items.', 'ctx-events'),
                'categories' => ['ctx-events'],
                'viewportWidth' => 960,
                'content' => self::loadPatternFile('event-details.html'),
            ]
        );
	}

	private static function loadPatternFile(string $filename): string
    {
        $path = PluginInfo::getPluginDir(self::PATTERN_DIR . '/' . $filename);
        if (!is_readable($path)) {
            return '';
        }

        $content = file_get_contents($path);
        if (!is_string($content) || $content === '') {
            return '';
        }

        return $content;
    }
}