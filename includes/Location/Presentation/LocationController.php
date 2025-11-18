<?php

namespace Contexis\Events\Location\Presentation;

use Contexis\Events\Location\Application\GetLocation;
use Contexis\Events\Location\Application\ListLocations;
use Contexis\Events\Location\Application\LocationIncludes;
use Contexis\Events\Shared\Infrastructure\Wordpress\ViewContextFactory;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class LocationController implements RestController
{
    public function __construct(
        private GetLocation $getLocation,
        private ListLocations $listLocations
    ) {
        $this->getLocation = $getLocation;
        $this->listLocations = $listLocations;
    }


    public function register(): bool
    {
        return register_rest_route('/events/v3', '/location/(?P<id>\d+)', array(
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

        $location_dto = $this->getLocation->execute($event_id, $include, ViewContextFactory::createFromCurrentUser());

        if (!$location_dto) {
            return new \WP_REST_Response(['message' => 'Location not found'], 404);
        }

        $location_resource = new LocationResource($location_dto);

        return new \WP_REST_Response($location_resource, 200);
    }
}
