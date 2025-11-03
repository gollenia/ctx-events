<?php

namespace Contexis\Events\Presentation\REST;

use Contexis\Events\Application\UseCases\GetEvent;
use Contexis\Events\Presentation\Resources\EventResource;

final class EventController implements RestAdapter {

	private GetEvent $getEvent;

	public function __construct(GetEvent $getEvent) {
		$this->getEvent = $getEvent;
	}

	public function register(): void {
        register_rest_route( '/events/v3', '/event/(?P<id>\d+)', array(
            
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_item' ),
                'permission_callback' => '__return_true'
            
        ) );

	}

	public function get_item(\WP_REST_Request $request): \WP_REST_Response {
		$event_id = (int) $request->get_param('id');
		$event_dto = $this->getEvent->execute($event_id);

		if (!$event_dto) {
			return new \WP_REST_Response(['message' => 'Event not found'], 404);
		}

		$event_resource = new EventResource($event_dto);

		return new \WP_REST_Response($event_resource, 200);
	}
}