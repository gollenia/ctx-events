<?php

namespace Contexis\Events\Shared\Presentation;

use Contexis\Events\Platform\Wordpress\OptionsRegistrar;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class OptionController implements RestController
{
    public function __construct(
        private OptionsRegistrar $definitions,
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

        $values = [];
        foreach ($fields as $key => $field) {
            $values[$key] = get_option($key, $field['default'] ?? null);
        }

        return new \WP_REST_Response([
            'fields' => $fields,
            'values' => $values,
        ]);
    }

    public function update(\WP_REST_Request $request): \WP_REST_Response
    {
        $fields = $this->definitions->all();
        $body   = $request->get_param('values') ?? [];

        foreach ($fields as $key => $field) {
            if (!array_key_exists($key, $body)) {
                continue;
            }

            update_option($key, $body[$key]);
        }

        return new \WP_REST_Response(['success' => true]);
    }
}
