<?php

namespace Contexis\Events\Presentation\REST;

final class EventsController implements RestController {
	public function register(): void {
		register_rest_route( '/events/v3', '/event(?:/(?P<id>\d+))?', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
            ),
        ) );
	}

	public function get_events( \WP_REST_Request $request ): \WP_REST_Response {
		$args = [
			'post_type'      => 'event',
			'posts_per_page' => 10,
		];

		$events = get_posts( $args );

		$data = [];
		foreach ( $events as $event ) {
			$data[] = [
				'id'    => $event->ID,
				'title' => get_the_title( $event->ID ),
			];
		}

		return new \WP_REST_Response( $data, 200 );
	}
}