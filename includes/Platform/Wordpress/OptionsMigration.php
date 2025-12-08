<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

final class OptionsMigration
{
    private const EVENT_OPTIONS_VERSION = '1.0.0';

    private array $options = [];

    public function __construct(OptionsRegistrar $options)
    {
        $this->options = $options->all();
    }

    public function migrate(): void
    {
        $current = get_option('ctx_events_options_version', '0.0.0');

        if (version_compare($current, self::EVENT_OPTIONS_VERSION, '>=')) {
            return;
        }

        $this->installNewOptions();
        $this->deleteDeprecated();

        update_option('ctx_events_options_version', self::EVENT_OPTIONS_VERSION);
    }

    private function installNewOptions(): void
    {
        foreach ($this->options as $key => $field) {
            $existing = get_option($key, null);
            if ($existing !== null) {
                continue;
            }

            $default = $field['default'] ?? null;

            add_option($key, $default);
        }
    }

    private function deleteDeprecated(): void
    {
        $all = $this->getAllFromDatabase();

        foreach ($all as $key => $value) {
            if (!array_key_exists($key, $this->options)) {
                    delete_option($key);
            }
        }
    }

    public function uninstall(): void
    {
        $all = $this->getAllFromDatabase();

        foreach ($all as $key => $value) {
            delete_option($key);
        }

        delete_option('ctx_events_options_version');
    }

    private function getAllFromDatabase(): array
    {
        $all = wp_load_alloptions();
        $result = [];
        foreach ($all as $key => $value) {
            if (!str_starts_with($key, 'ctx_events_')) {
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }
}
