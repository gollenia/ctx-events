<?php

namespace Contexis\Events\Booking\Presentation;

use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class BookingController implements RestController
{

	private array $base_args = [
		'uuid' => [
			'required'    => true,
			'type'        => 'string',
			'description' => 'unique booking identifier',
			'sanitize_callback' => 'sanitize_text_field'
		]
	];

	public function register(): void
	{
		register_rest_route('events/v3', '/bookings', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [$this, 'listBookings'],
				'permission_callback' => [$this, 'checkBookingAdminPermission'],
        	],
			[
                'methods'             => 'POST',
                'callback'            => [$this, 'createBooking'],
                'permission_callback' => [$this, 'checkBookingPermission'],
                'args'                => [
					'nonce'        => [ 'required' => true, 'type' => 'string' ],
					'event_id'    => [ 'required' => true, 'type' => 'integer' ],
                    'registration' => [ 'required' => true, 'type' => 'object' ],
					'attendees'    => [ 'required' => true, 'type' => 'array' ],
					'gateway'      => [ 'required' => true, 'type' => 'string' ],
					'coupon_code'  => [ 'required' => false, 'type' => 'string' ]
                ]
            ]
		]);

		register_rest_route('events/v3', '/bookings/(?P<uuid>\w+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'editBooking'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => $this->base_args,
            ],
			
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'updateBooking'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => array_merge($this->base_args, [
                    'registration' => [ 'required' => true, 'type' => 'object' ],
					'attendees'    => [ 'required' => true, 'type' => 'array' ],
					'gateway'      => [ 'required' => true, 'type' => 'string' ],
					'coupon_code'  => [ 'required' => false, 'type' => 'string' ]
                ]),
            ],
            [
                'methods'             => 'PATCH',
                'callback'            => [$this, 'setBookingStatus'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => array_merge($this->base_args, [
                    'status' => ['required' => true, 'type' => 'string']
                ]),
            ],
        ]);
	}

	public function listBookings() {}

	public function editBooking($request) {}

	public function createBooking(\WP_REST_Request$request): \WP_REST_Response {
		$data = $request->get_params();

		// Here you would typically call a service to handle the booking creation logic.
		// For demonstration purposes, we'll just return the received data.

		$response_data = [
			'message' => 'Booking created successfully',
			'data'    => $data,
		];

		return new \WP_REST_Response($response_data, 201);
	}

	public function updateBooking($request) {}

	public function setBookingStatus($request) {}

	public function checkBookingPermission() {
		return current_user_can('edit_posts');
	}

	public function checkBookingAdminPermission() {
		return current_user_can('manage_options');
	}
}