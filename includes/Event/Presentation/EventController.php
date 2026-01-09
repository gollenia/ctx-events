<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\EventIncludes;
use Contexis\Events\Event\Application\GetEvent;
use Contexis\Events\Event\Application\ListEvents;
use Contexis\Events\Shared\Infrastructure\Wordpress\UserContextFactory;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use Contexis\Events\Shared\Presentation\Links;

final class EventController implements RestController
{
    public function __construct(
        private GetEvent $getEvent,
        private ListEvents $listEvents
    ) {
        $this->getEvent = $getEvent;
        $this->listEvents = $listEvents;
    }

    public function register(): void
    {
        $route = Links::restRoute('events', '(?P<id>\d+)');

        register_rest_route($route['ns'], $route['path'], [
            'methods'   => \WP_REST_Server::READABLE,
            'callback'  => [$this, 'getItem'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'description' => 'The ID of the event to retrieve.',
                    'type' => 'integer',
                ],
                'include' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => ['location', 'image', 'available', 'bookable', 'categories', 'tags', 'locations', 'persons', 'all' ]
                        ],
                ],
            ],
        ]);

        register_rest_route('/events/v3', '/events', [
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'getEventPage'],
                'permission_callback' => '__return_true',
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'default' => 10,
                    ],
                    'status' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => ['publish', 'future', 'draft', 'pending', 'private', 'trash']
                        ],
                    ],
                    'include' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => ['location', 'image', 'available', 'bookable', 'categories', 'tags', 'locations', 'persons', 'all' ]
                        ],
                    ],
                    'order_by' => [
                        'type' => 'string',
                        'default' => 'date-time',
                    ],
                    'order' => [
                        'type' => 'string',
                        'default' => 'DESC',
                    ],
                    'scope' => [
                        'type' => 'string',
                        'default' => 'future',
                    ],
                    'categories'  =>  [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                    ],
                    'tags'       => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                    ],
                    'location'  => [
                        'type' => 'integer',
                        'default' => null
                    ],
                    'persons'    => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                    ],
                    'bookable' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'availibility' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
					'price' => [
                        'type' => 'integer',
                        'default' => null,
                    ],
                    'search' => [
                        'type' => 'string'
					]
                ]
            ],
        ]);

		register_rest_route('/events/v3', '/events/(?P<id>\d+)/prepare-booking', [
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'prepareBooking'],
                'permission_callback' => '__return_true',
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ],
        ]);
    }



    public function getItem(\WP_REST_Request $request): \WP_REST_Response
    {
        $event_id = (int) $request->get_param('id');
        $include = EventIncludes::fromArray(explode(',', $request->get_param('include') ?? ''));

        $event_dto = $this->getEvent->execute($event_id, $include, UserContextFactory::createFromCurrentUser());

        if (!$event_dto) {
            return new \WP_REST_Response(['message' => 'Event not found'], 404);
        }

        $event_resource = new EventResource($event_dto);

        return new \WP_REST_Response($event_resource, 200);
    }

    public function getEventPage(\WP_REST_Request $request): \WP_REST_Response
    {
        $userContext = UserContextFactory::createFromCurrentUser();
        $query = EventCriteriaMapper::fromRequest($request, $userContext);

        $page = $this->listEvents->execute($query);

        $result = [];
        foreach ($page as $index => $event_dto) {
            $result[] = new EventResource($event_dto);
        }

        $response = new \WP_REST_Response($result, 200);
        $response->header('X-WP-Total', $page->pagination()->totalItems);
        $response->header('X-WP-TotalPages', $page->pagination()->totalPages());
        return $response;
    }

	public function prepareBooking(\WP_REST_Request $request): \WP_REST_Response
	{
		$event_id = (int) $request->get_param('id');
		
		return new \WP_REST_Response(['message' => 'Die Stubsmaus'], 200);
	}
}
