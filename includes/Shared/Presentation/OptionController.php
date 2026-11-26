<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation;

use Contexis\Events\Platform\Wordpress\OptionsMigration;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class OptionController implements RestController
{
    public function __construct(
        private OptionsMigration $definitions,
    ) {
    }

    public function register(): void
    {
        register_rest_route(
            'events/v3',
            '/options',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'get'],
                    'permission_callback' => fn () => current_user_can('manage_options'),
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'update'],
                    'permission_callback' => fn () => current_user_can('manage_options'),
                ],
            ]
        );
    }

    public function get(\WP_REST_Request $request): \WP_REST_Response
    {
        $fields = $this->definitions->all();

        $result = [];
        foreach ($fields as $key => $field) {
            unset($field['normalize']);
            $field['value'] = get_option($key, $field['default'] ?? null);
            $field['key'] = $key;
            $result[] = $field;
        }

        return new \WP_REST_Response($result);
    }

    public function update(\WP_REST_Request $request): \WP_REST_Response
    {
        $fields = $this->definitions->all();
        $body   = $request->get_param('values') ?? [];

        if (!is_array($body)) {
            return new \WP_REST_Response(['message' => __('Invalid settings.', 'ctx-events')], 400);
        }

        $values = [];
        try {
            foreach ($fields as $key => $field) {
                if (!array_key_exists($key, $body)) {
                    continue;
                }

                $normalize = $field['normalize'] ?? null;
                $values[$key] = is_callable($normalize) ? $normalize($body[$key]) : $body[$key];
            }
        } catch (\InvalidArgumentException $error) {
            return new \WP_REST_Response(['message' => $error->getMessage()], 400);
        }

        foreach ($values as $key => $value) {
            update_option($key, $value);
        }

        return new \WP_REST_Response(['success' => true]);
    }
}
