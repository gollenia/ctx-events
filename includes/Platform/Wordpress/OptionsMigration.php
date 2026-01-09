<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class OptionsMigration implements Registrar
{
    private const EVENT_OPTIONS_VERSION = '1.0.0';
	private array $definedOptions = [];

	/*
	 * @var iterable<OptionProvider>
	 */
	public function __construct(
		private readonly iterable $providers,
	)
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'fields')) {
                $this->definedOptions = array_merge($this->definedOptions, $provider->fields());
            }
        }
    }

	public function hook(): void
    {
        add_action('admin_init', [$this, 'migrate']);
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
        foreach ($this->definedOptions as $key => $field) {
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
            if (!array_key_exists($key, $this->definedOptions)) {
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

	public function all(): array
    {
        return $this->definedOptions;
    }
}
