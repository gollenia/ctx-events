<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Presentation;

use Contexis\Events\Location\Application\GetLocation;
use Contexis\Events\Location\Application\ListLocations;
use Contexis\Events\Location\Application\LocationIncludes;
use Contexis\Events\Location\Presentation\Resources\LocationResource;
use Contexis\Events\Shared\Infrastructure\Wordpress\UserContextFactory;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class LocationController implements RestController
{
    public function __construct(
        private GetLocation $getLocation
    ) {
        $this->getLocation = $getLocation;
    }


    public function register(): void
    {
        register_rest_route('events/v3', '/location/(?P<id>\d+)', array(
            'methods'   => \WP_REST_Server::READABLE,
            'callback'  => [$this, 'getItem'],
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'description' => 'The ID of the event to retrieve.',
                    'type' => 'integer',
                ),
                'include' => array(
                    'required' => false,
                    'description' => 'Comma-separated list of related resources to include (e.g., "media").',
                    'type' => 'string',
                )
            ),
        ));
    }

    public function getItem(\WP_REST_Request $request): \WP_REST_Response
    {
        $event_id = (int) $request->get_param('id');
        $include = LocationIncludes::fromArray(explode(',', $request->get_param('include') ?? ''));

        $location_dto = $this->getLocation->execute($event_id, $include, UserContextFactory::createFromCurrentUser());

        if (!$location_dto) {
            return new \WP_REST_Response(['message' => 'Location not found'], 404);
        }

        $location_resource = LocationResource::fromDto($location_dto);

        return new \WP_REST_Response($location_resource, 200);
    }
}
