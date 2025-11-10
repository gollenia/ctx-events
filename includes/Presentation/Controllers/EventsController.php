<?php

namespace Contexis\Events\Presentation\Controllers;

use Contexis\Events\Presentation\Requests\ListEventsRequest;
use Contexis\Events\Presentation\Security\ViewContextFactory;

final class EventsController implements RestController {
	public function register(): void {
		register_rest_route( '/events/v3', '/events', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_events_page' ),
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

	public function get_events_page( \WP_REST_Request $request ): \WP_REST_Response 
	{	
		$request = ListEventsRequest::fromParams($request->get_params());
		var_dump($request);
		$view = ViewContextFactory::createFromCurrentUser();
		return new \WP_REST_Response( $view, 200 );
	}
}	