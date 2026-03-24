<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Application\UseCases\PrepareBooking;
use Contexis\Events\Event\Application\UseCases\GetEvent;
use Contexis\Events\Event\Application\UseCases\CancelEvent;
use Contexis\Events\Event\Application\UseCases\ListEvents;
use Contexis\Events\Event\Presentation\Resources\EventResource;
use Contexis\Events\Event\Presentation\Resources\PrepareBookingResource;
use Contexis\Events\Shared\Infrastructure\Wordpress\UserContextFactory;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use Contexis\Events\Shared\Presentation\Links;
use Contexis\Events\Shared\Presentation\RestRoute;

final class EventController implements RestController
{
	private RestRoute $route;
    public function __construct(
        private GetEvent $getEvent,
        private ListEvents $listEvents,
		private CancelEvent $cancelEvent,
		private PrepareBooking $prepareBooking,
    ) {
        $this->route = RestRoute::forType('events');
    }

    public function register(): void
    {
		$args = $this->route->getForSingle();

        register_rest_route($args->namespace, $args->route, args: [
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
                        'enum' => ['location', 'image', 'available', 'author', 'bookings', 'categories', 'tags', 'locations', 'persons', 'all' ]
                    ],
                ],
            ],
        ]);

		register_rest_route($args->namespace, $args->route, args: [
			[
				'methods'   => \WP_REST_Server::DELETABLE,
				'callback'  => [$this, 'deleteEvent'],
				'permission_callback' => '__return_true',
				'args' => [
					'id' => [
						'required' => true,
						'description' => 'The ID of the event to delete.',
						'type' => 'integer',
					]
				],
			],
		]);

		$args = $this->route->getForSingle('/cancel');
		register_rest_route($args->namespace, $args->route, args: [
			[
				'methods'   => 'POST',
				'callback'  => [$this, 'cancelEvent'],
				'permission_callback' => '__return_true',
				'args' => [
					'id' => [
						'required' => true,
						'description' => 'The ID of the event to cancel.',
						'type' => 'integer',
					],
					'notifyAttendees' => [
						'required' => false,
						'description' => 'Whether to notify attendees about the cancellation.',
						'type' => 'boolean',
						'default' => false,
					],
					'attendee_message' => [
						'required' => false,
						'sanitize_callback' => 'sanitize_text_field',
						'description' => 'An optional message to include in the cancellation notification sent to attendees.',
						'type' => 'string',
						'default' => '',
					]
				],
			],
		]);

        $args = $this->route->getForCollection();
        register_rest_route($args->namespace, $args->route, args: [
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
                            'enum' => ['location', 'image', 'available', 'author', 'bookings', 'categories', 'tags', 'locations', 'persons', 'all' ]
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
                        'default' => null,
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

		$args = $this->route->getForSingle('/prepare-booking');
		register_rest_route($args->namespace, $args->route, args: [
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
		$include = EventIncludeRequest::fromArray($this->normalizeIncludeParam($request->get_param('include')));
		$userContext = UserContextFactory::createFromCurrentUser();
		$response = $this->getEvent->execute($event_id, $include, $userContext);

        if (!$response) {
            return new \WP_REST_Response(['message' => 'Event not found'], 404);
        }

        $event_resource = EventResource::fromDto($response, $this->route);

        return new \WP_REST_Response($event_resource, 200);
    }

    public function getEventPage(\WP_REST_Request $request): \WP_REST_Response
    {
        $userContext = UserContextFactory::createFromCurrentUser();
        $criteria = EventCriteriaMapper::fromRequest($request, $userContext);
		$includes = EventIncludeRequest::fromArray($request->get_param('include') ?? []);

        $page = $this->listEvents->execute($criteria, $includes, $userContext);

        $result = [];
        foreach ($page as $index => $event_dto) {
			
            $result[] = EventResource::fromDto($event_dto, $this->route);
        }

        $response = new \WP_REST_Response($result, 200);
        $response->header('X-WP-Total', (string) $page->pagination()->totalItems);
        $response->header('X-WP-TotalPages', (string) $page->pagination()->totalPages());
		$response->header('X-WP-StatusCounts', json_encode($page->statusCounts()?->toArray()));
		
        return $response;
    }

    public function prepareBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $event_id = (int) $request->get_param('id');
		$userContext = UserContextFactory::createFromCurrentUser();

		try {
			$response = $this->prepareBooking->execute($event_id, $userContext);
		} catch (\DomainException $e) {
			return new \WP_REST_Response(['message' => $e->getMessage()], 422);
		}

		if ($response === null) {
			return new \WP_REST_Response(['message' => 'Event not found'], 404);
		}

		return new \WP_REST_Response(PrepareBookingResource::fromResponse($response)->toArray(), 200);
    }

	public function cancelEvent(\WP_REST_Request $request): \WP_REST_Response
	{
		$event_id = (int) $request->get_param('id');
		$notify_attendees = (bool) $request->get_param('notifyAttendees');
		$attendee_message = (string) $request->get_param('attendee_message');
		$result = $this->cancelEvent->execute($event_id);


		return new \WP_REST_Response(['message' => 'Event cancelled'], 200);
	}

	public function deleteEvent(\WP_REST_Request $request): \WP_REST_Response
	{
		$event_id = (int) $request->get_param('id');

		return new \WP_REST_Response(['message' => 'Event deleted'], 200);
	}

	private function normalizeIncludeParam(mixed $includeParam): array
	{
		if ($includeParam === null) {
			return [];
		}

		if (is_array($includeParam)) {
			return array_values(array_filter(
				$includeParam,
				static fn (mixed $value): bool => is_string($value) && $value !== ''
			));
		}

		if (!is_string($includeParam) || $includeParam === '') {
			return [];
		}

		$parts = explode(',', $includeParam);

		return array_values(array_filter($parts, static fn (string $value): bool => $value !== ''));
	}
}
