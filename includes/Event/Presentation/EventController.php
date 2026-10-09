<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Application\UseCases\GetEventCalendar;
use Contexis\Events\Event\Application\UseCases\PrepareBooking;
use Contexis\Events\Event\Application\UseCases\GetEvent;
use Contexis\Events\Event\Application\UseCases\CancelEvent;
use Contexis\Events\Event\Application\UseCases\DuplicateEvent;
use Contexis\Events\Event\Application\UseCases\ListEvents;
use Contexis\Events\Event\Application\UseCases\ListOccurrences;
use Contexis\Events\Event\Application\UseCases\DetachRecurringOccurrence;
use Contexis\Events\Event\Application\UseCases\CancelRecurringOccurrence;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Presentation\Resources\EventResource;
use Contexis\Events\Event\Presentation\Resources\OccurrenceResource;
use Contexis\Events\Event\Presentation\Resources\EventCalendarEntryResource;
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
		private ListOccurrences $listOccurrences,
		private DetachRecurringOccurrence $detachRecurringOccurrence,
		private CancelRecurringOccurrence $cancelRecurringOccurrence,
		private GetEventCalendar $getEventCalendar,
		private CancelEvent $cancelEvent,
		private DuplicateEvent $duplicateEvent,
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
				'permission_callback' => [$this, 'checkEditPermission'],
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
				'permission_callback' => [$this, 'checkEditPermission'],
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

		$args = $this->route->getForSingle('/duplicate');
		register_rest_route($args->namespace, $args->route, args: [[
			'methods' => 'POST',
			'callback' => [$this, 'duplicateEvent'],
			'permission_callback' => [$this, 'checkEditPermission'],
			'args' => [
				'id' => [
					'required' => true,
					'description' => 'The ID of the event to duplicate.',
					'type' => 'integer',
				],
				'dates' => [
					'required' => true,
					'description' => 'The dates for the event copies.',
					'type' => 'array',
					'items' => ['type' => 'string', 'format' => 'date'],
				],
			],
		]]);

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
                    ],
                    'with_recurrences' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Opt in to a merged list of real and virtual recurring occurrences.',
                    ],
                    'recurring_event' => [
                        'type' => 'integer',
                        'minimum' => 1,
                        'description' => 'Limit projected occurrences to one recurring-event post.',
                    ]
                ]
            ],
        ]);

		$calendarArgs = $this->route->getForCollection('/calendar');
		register_rest_route($calendarArgs->namespace, $calendarArgs->route, args: [[
			'methods' => 'GET',
			'callback' => [$this, 'getCalendarEntries'],
			'permission_callback' => '__return_true',
			'args' => [
				'start_date' => [
					'type' => 'string',
					'required' => true,
				],
				'end_date' => [
					'type' => 'string',
					'required' => true,
				],
				'categories' => [
					'type' => 'array',
					'items' => ['type' => 'integer'],
				],
				'location' => [
					'type' => 'integer',
					'default' => null,
				],
				'person' => [
					'type' => 'integer',
					'default' => null,
				],
			],
		]]);

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

		$args = $this->route->getForSingle('/detach-occurrence');
		register_rest_route($args->namespace, $args->route, args: [[
			'methods' => 'POST',
			'callback' => [$this, 'detachOccurrence'],
			'permission_callback' => [$this, 'checkEditPermission'],
			'args' => [
				'id' => ['required' => true, 'type' => 'integer'],
				'occurrence_key' => ['required' => true, 'type' => 'string'],
			],
		]]);

		$args = $this->route->getForSingle('/cancel-occurrence');
		register_rest_route($args->namespace, $args->route, args: [[
			'methods' => 'POST',
			'callback' => [$this, 'cancelOccurrence'],
			'permission_callback' => [$this, 'checkEditPermission'],
			'args' => [
				'id' => ['required' => true, 'type' => 'integer'],
				'occurrence_key' => ['required' => true, 'type' => 'string'],
			],
		]]);
    }

	public function checkEditPermission(\WP_REST_Request $request): bool
	{
		return current_user_can('edit_post', (int) $request->get_param('id'));
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
        if ((bool) $request->get_param('with_recurrences')) {
            return $this->getOccurrencePage($request);
        }

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

    private function getOccurrencePage(\WP_REST_Request $request): \WP_REST_Response
    {
        $userContext = UserContextFactory::createFromCurrentUser();
        $criteria = EventCriteriaMapper::fromRequest($request, $userContext);

        try {
            $page = $this->listOccurrences->execute($criteria);
        } catch (\InvalidArgumentException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 400);
        }

        $result = array_map(
            fn ($occurrence) => OccurrenceResource::fromDto($occurrence, $this->route)->toArray(),
            $page->toArray(),
        );

        $response = new \WP_REST_Response($result, 200);
        $response->header('X-WP-Total', (string) $page->pagination()->totalItems);
        $response->header('X-WP-TotalPages', (string) $page->pagination()->totalPages());

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
		$success = $this->cancelEvent->execute($event_id, $notify_attendees, $attendee_message);

		if (!$success) {
			return new \WP_REST_Response(['message' => 'Event not found'], 404);
		}

		return new \WP_REST_Response(['message' => 'Event cancelled'], 200);
	}

	public function duplicateEvent(\WP_REST_Request $request): \WP_REST_Response
	{
		$dates = $request->get_param('dates');
		if (!is_array($dates) || $dates === []) {
			return new \WP_REST_Response(['message' => 'At least one date is required'], 422);
		}

		$startDates = [];
		foreach ($dates as $date) {
			$startDate = $this->dateFromRequest($date);
			if ($startDate === null) {
				return new \WP_REST_Response(['message' => 'Invalid event date'], 422);
			}

			$startDates[] = $startDate;
		}

		$newEventIds = $this->duplicateEvent->execute(
			EventId::from((int) $request->get_param('id')),
			...$startDates,
		);
		if ($newEventIds === []) {
			return new \WP_REST_Response(['message' => 'Event not found'], 404);
		}

		return new \WP_REST_Response([
			'message' => 'Events duplicated',
			'ids' => array_map(static fn (EventId $eventId): int => $eventId->toInt(), $newEventIds),
		], 200);
	}

	public function detachOccurrence(\WP_REST_Request $request): \WP_REST_Response
	{
		try {
			$occurrenceKey = OccurrenceKey::fromString((string) $request->get_param('occurrence_key'));
			$eventId = $this->detachRecurringOccurrence->execute(
				new RecurrenceId((int) $request->get_param('id')),
				$occurrenceKey,
			);
		} catch (\InvalidArgumentException|\DomainException $exception) {
			return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
		} catch (\RuntimeException $exception) {
			return new \WP_REST_Response(['message' => $exception->getMessage()], 500);
		}

		return new \WP_REST_Response(['eventId' => $eventId->toInt()], 200);
	}

	public function cancelOccurrence(\WP_REST_Request $request): \WP_REST_Response
	{
		try {
			$this->cancelRecurringOccurrence->execute(
				new RecurrenceId((int) $request->get_param('id')),
				OccurrenceKey::fromString((string) $request->get_param('occurrence_key')),
			);
		} catch (\InvalidArgumentException|\DomainException $exception) {
			return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
		} catch (\RuntimeException $exception) {
			return new \WP_REST_Response(['message' => $exception->getMessage()], 500);
		}

		return new \WP_REST_Response(['message' => 'Recurring occurrence cancelled'], 200);
	}

	public function deleteEvent(\WP_REST_Request $request): \WP_REST_Response
	{
		$event_id = (int) $request->get_param('id');

		return new \WP_REST_Response(['message' => 'Event deleted'], 200);
	}

	/** @return array<string> */
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

	private function dateFromRequest(mixed $value): ?\DateTimeImmutable
	{
		if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
			return null;
		}

		$date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, wp_timezone());
		$errors = \DateTimeImmutable::getLastErrors();
		if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
			return null;
		}

		return $date;
	}

	public function getCalendarEntries(\WP_REST_Request $request): \WP_REST_Response
	{
		try {
			$startDate = new \DateTimeImmutable((string) $request->get_param('start_date'), wp_timezone());
			$endDate = new \DateTimeImmutable((string) $request->get_param('end_date'), wp_timezone());
		} catch (\Exception) {
			return new \WP_REST_Response(['message' => 'Invalid date range'], 400);
		}

		$entries = $this->getEventCalendar->execute(
			startDate: $startDate,
			endDate: $endDate,
			categories: array_map('intval', $request->get_param('categories') ?? []),
			locationId: $request->has_param('location') ? (int) $request->get_param('location') : null,
			personId: $request->has_param('person') ? (int) $request->get_param('person') : null,
		);

		$result = array_map(
			static fn ($entry) => EventCalendarEntryResource::fromDto($entry)->toArray(),
			$entries,
		);

		return new \WP_REST_Response($result, 200);
	}
}
