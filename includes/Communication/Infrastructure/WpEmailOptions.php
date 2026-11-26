<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailOptions;
use Contexis\Events\Shared\Infrastructure\Wordpress\WpOptions;
use InvalidArgumentException;

final class WpEmailOptions extends WpOptions implements EmailOptions
{
    private const LAYOUT = 'ctx_events_email_layout';
    private const CSS = 'ctx_events_email_css';

    public function fields(): array
    {
        return [
            self::LAYOUT => [
                'type' => 'text',
                'editor' => 'html',
                'domain' => 'emails',
                'order' => 10,
                'default' => EmailLayout::PLACEHOLDER,
                'label' => __('Email layout', 'ctx-events'),
                'description' => __('Enter HTML with {{content}} where the email message should appear.', 'ctx-events'),
                'normalize' => self::normalizeLayout(...),
            ],
            self::CSS => [
                'type' => 'text',
                'editor' => 'css',
                'domain' => 'emails',
                'order' => 20,
                'default' => '',
                'label' => __('Custom email CSS', 'ctx-events'),
                'description' => __('CSS rules for all Events emails. Enter CSS without style tags.', 'ctx-events'),
                'normalize' => self::normalizeCss(...),
            ],
        ];
    }

    public function layout(): string
    {
        try {
            return self::normalizeLayout(get_option(self::LAYOUT, ''));
        } catch (InvalidArgumentException) {
            return EmailLayout::PLACEHOLDER;
        }
    }

    public function css(): string
    {
        try {
            return self::normalizeCss(get_option(self::CSS, ''));
        } catch (InvalidArgumentException) {
            return '';
        }
    }

    private static function normalizeLayout(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(__('The email layout must be HTML text.', 'ctx-events'));
        }

        if (trim($value) === '') {
            return EmailLayout::PLACEHOLDER;
        }

        $html = wp_kses_post($value);
        if (EmailLayout::placeholder(EmailLayout::fragment($html)) === null) {
            throw new InvalidArgumentException(__('Put {{content}} exactly once in the email layout as text, outside attributes and comments.', 'ctx-events'));
        }

        return $html;
    }

    private static function normalizeCss(mixed $value): string
    {
        if (!is_string($value) || preg_match('~</?style\b~i', $value) === 1) {
            throw new InvalidArgumentException(__('Enter email CSS as text without style tags.', 'ctx-events'));
        }

        return $value;
    }
}
