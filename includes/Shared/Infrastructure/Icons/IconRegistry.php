<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Icons;

final class IconRegistry
{
    /** @var array<string, string> */
    private array $icons = [];

    private bool $booted = false;

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        do_action('ctx_icons_register', $this);
        do_action('ctx_icons_override', $this);

        $icons = apply_filters('ctx_icons', $this->icons, $this);

        $this->icons = is_array($icons) ? array_filter($icons, 'is_string') : $this->icons;
        $this->booted = true;
    }

    public function register(string $name, string $markup, string $source = ''): bool
    {
        $name = trim($name);

        if ($name === '' || $markup === '' || isset($this->icons[$name])) {
            return false;
        }

        $this->icons[$name] = $markup;

        return true;
    }

    public function registerFromFile(string $name, string $path, string $source = ''): bool
    {
        $markup = $this->readMarkupFromFile($path);

        if ($markup === '') {
            return false;
        }

        return $this->register($name, $markup, $source);
    }

    public function override(string $name, string $markup, string $source = ''): void
    {
        $name = trim($name);

        if ($name === '' || $markup === '') {
            return;
        }

        $this->icons[$name] = $markup;
    }

    public function overrideFromFile(string $name, string $path, string $source = ''): void
    {
        $markup = $this->readMarkupFromFile($path);

        if ($markup === '') {
            return;
        }

        $this->override($name, $markup, $source);
    }

    public function resolveSlot(string $icon): string
    {
        return trim($icon);
    }

    /** @return array<string, string> */
    public function getIcons(): array
    {
        $this->boot();

        return $this->icons;
    }

    public function getIconMarkup(string $icon): string
    {
        $this->boot();

        $slot = $this->resolveSlot($icon);

        if ($slot === '') {
            return '';
        }

        $icons = $this->getIcons();

        return $icons[$slot] ?? '';
    }

    /** @return array<string, string> */
    public function getEditorIcons(): array
    {
        return $this->getIcons();
    }

    private function readMarkupFromFile(string $path): string
    {
        $path = trim($path);

        if ($path === '' || !is_file($path)) {
            return '';
        }

        return (string) file_get_contents($path);
    }
}
