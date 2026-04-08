<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Platform\Wordpress\PluginInfo;
use Contexis\Events\Shared\Infrastructure\Icons\IconRegistry;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use Contexis\Events\Shared\Presentation\RestRoute;

final class IconsController implements RestController
{
    private RestRoute $route;

    public function __construct(
        private readonly IconRegistry $iconRegistry,
    ) {
        $this->route = RestRoute::forType('icons');
    }

    public function register(): void
    {
        $args = $this->route->getForCollection();

        register_rest_route($args->namespace, $args->route, args: [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'getIcons'],
                'permission_callback' => '__return_true',
                'args' => [
                    'names' => [
                        'required' => false,
                        'description' => 'Optional icon names as comma-separated string or repeated query parameter.',
                    ],
                ],
            ],
        ]);
    }

    public function getIcons(\WP_REST_Request $request): \WP_REST_Response
    {
        $requestedNames = $this->normalizeNames($request->get_param('names'));
        $icons = $this->filterIcons($requestedNames);
        $missing = $this->getMissingNames(array_keys($icons), $requestedNames);

        $response = new \WP_REST_Response([
            'icons' => $icons,
            'missing' => $missing,
            'version' => PluginInfo::getPluginVersion(),
        ], 200);

        $response->header('Cache-Control', 'public, max-age=3600');
        $response->header('ETag', '"' . md5((string) wp_json_encode([$icons, $missing, PluginInfo::getPluginVersion()])) . '"');

        return $response;
    }

    /**
     * @param array<int, string> $names
     * @return array<string, string>
     */
    private function filterIcons(array $names): array
    {
        if ($names === []) {
            return $this->iconRegistry->getIcons();
        }

        $filtered = [];

        foreach ($names as $name) {
            $markup = $this->iconRegistry->getIconMarkup($name);

            if ($markup !== '') {
                $filtered[$name] = $markup;
            }
        }

        return $filtered;
    }

    /**
     * @param array<int, string> $resolved
     * @param array<int, string> $requested
     * @return array<int, string>
     */
    private function getMissingNames(array $resolved, array $requested): array
    {
        if ($requested === []) {
            return [];
        }

        return array_values(array_diff($requested, $resolved));
    }

    /**
     * @param array<int, string>|string|null $namesParam
     * @return array<int, string>
     */
    private function normalizeNames(array|string|null $namesParam): array
    {
        if (is_string($namesParam)) {
            $namesParam = explode(',', $namesParam);
        }

        if (!is_array($namesParam)) {
            return [];
        }

        $names = array_map(
            static fn (mixed $value): string => trim((string) $value),
            $namesParam,
        );

        $names = array_filter($names, static fn (string $name): bool => $name !== '');

        return array_values(array_unique($names));
    }
}
