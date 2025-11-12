<?php

namespace Contexis\Events\Presentation\Controllers;

use Contexis\Events\Application\Query\ListEventsQuery;
use Contexis\Events\Application\UseCases\GetEventPage;
use Contexis\Events\Presentation\Factories\ListEventsQueryFactory;
use Contexis\Events\Presentation\Factories\ViewContextFactory;

class EventListController implements RestAdapter
{
	private GetEventPage $listEvents;

	public function __construct(GetEventPage $listEvents)
	{
		$this->listEvents = $listEvents;
	}

	public function register(): void
	{
		register_rest_route( '/events/v3', '/events', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'getEventPage' ),
                'permission_callback' => fn($req) => true,
				'args' => [
					'page' => [
						'type' => 'integer',
						'default' => 1,
					],
					'per_page' => [
						'type' => 'integer',
						'default' => 10,
					],
					'include' => [
						'type' => 'array',
						'items' => [
							'type' => 'string',
							'enum' => ['location', 'image','available', 'bookable', 'categories', 'tags', 'locations', 'persons' ]
						],
					],
					'order_by' => [
						'type' => 'string',
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
						'type' => 'array',
						'items' => ['type' => 'integer'],
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
					'search' => [
						'type' => 'string'
					]
				]
            ),
        ) );
	}
	public function getEventPage( \WP_REST_Request $request ): \WP_REST_Response 
	{	
		$query = ListEventsQueryFactory::fromWpRequest($request);
		$view = ViewContextFactory::createFromCurrentUser();
		$page = $this->listEvents->execute($query, $view);
		return new \WP_REST_Response( $page, 200 );
	}
}