<?php

namespace Contexis\Events\Presentation\Controllers;

use Contexis\Events\Application\Query\EventIncludes;
use Contexis\Events\Application\UseCases\GetEvent;
use Contexis\Events\Application\UseCases\GetEvents;
use Contexis\Events\Domain\Collections\EventCollection;
use Contexis\Events\Presentation\Factories\ViewContextFactory;
use Contexis\Events\Presentation\Resources\EventResource;
use Contexis\Events\Presentation\Services\Links;
use Contexis\Events\Presentation\Requests\ListEventsRequest;

use WP;

final class EventController implements RestAdapter
{
    private GetEvent $getEvent;

    public function __construct(GetEvent $getEvent)
    {
        $this->getEvent = $getEvent;
    }

    public function register(): void
    {
        $route = Links::restRoute('event', '(?P<id>\d+)');

        register_rest_route($route['ns'], $route['path'], array(
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
                    'description' => 'Comma-separated list of related resources to include (e.g., "speakers,location,media").',
                    'type' => 'string',
                )
            ),
        ));

		
    }

    public function getItem(\WP_REST_Request $request): \WP_REST_Response
    {
        $event_id = (int) $request->get_param('id');
        $include = EventIncludes::fromArray(explode(',', $request->get_param('include') ?? ''));

        $event_dto = $this->getEvent->execute($event_id, $include, ViewContextFactory::createFromCurrentUser());

        if (!$event_dto) {
            return new \WP_REST_Response(['message' => 'Event not found'], 404);
        }

        $event_resource = new EventResource($event_dto);

        return new \WP_REST_Response($event_resource, 200);
    }

    public function sanitizationCallback(\WP_REST_Request $request): \WP_REST_Request
    {
        $event_id = $request->get_param('id');
        $request->set_param('id', is_numeric($event_id) ? (int)$event_id : 0);
        $include = $request->get_param('include');
        if (is_string($include)) {
            $request->set_param('include', EventIncludes::fromArray(explode(',', $include)));
        }
        return $request;
    }

	
}
