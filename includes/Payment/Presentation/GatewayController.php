<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation;

use Contexis\Events\Payment\Application\UseCases\EditGateway;
use Contexis\Events\Payment\Application\UseCases\ListGateways;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class GatewayController implements RestController
{
	/*
	 * TODO: Make this admin only
	 */
	public function __construct(
		private readonly EditGateway $editGateway,
		private readonly ListGateways $listGateways,
	) {}
	public function register(): void
    {
        register_rest_route('events/v3', '/gateways/(?P<slug>\w+)', [
            'methods'   => \WP_REST_Server::READABLE,
            'callback'  => [$this, 'editGateway'],
            'permission_callback' => '__return_true',
            'args' => [
                'slug' => [
                    'required' => true,
                    'description' => 'The slug of the gateway to retrieve.',
                    'type' => 'string',
                ]
            ],
			'permission_callback' => '__return_true',
        ]);

		register_rest_route('events/v3', '/gateways', [
            'methods'   => \WP_REST_Server::READABLE,
            'callback'  => [$this, 'listGateways'],
            'permission_callback' => '__return_true',
            'args' => [
                'active' => [
                    'required' => false,
                    'description' => 'The active status of the gateway to retrieve.',
                    'type' => 'boolean',
                ]
            ],
			'permission_callback' => '__return_true',
        ]);

		register_rest_route('events/v3', '/gateways', [
            'methods'   => \WP_REST_Server::EDITABLE,
            'callback'  => [$this, 'updateGateway'],
            'permission_callback' => '__return_true',
            'args' => [
                'slug' => [
                    'required' => true,
                    'description' => 'The slug of the gateway to update.',
                    'type' => 'string',
                ],
                'data' => [
                    'required' => true,
                    'description' => 'The data of the gateway to update.',
                    'type' => 'object',
                ]
            ],
			'permission_callback' => '__return_true',
        ]);
    }

	public function editGateway(\WP_REST_Request $request): \WP_REST_Response
	{
		$gateway = $this->editGateway->execute($request->get_param('slug'));
		if(!$gateway) {
			return new \WP_REST_Response([], 404);
		}
		return new \WP_REST_Response($gateway);
	}

	public function listGateways(\WP_REST_Request $request): \WP_REST_Response
	{
		$gateways = $this->listGateways->execute();
		return new \WP_REST_Response($gateways);
	}

	public function updateGateway(\WP_REST_Request $request): \WP_REST_Response
	{
		return new \WP_REST_Response([]);
	}
}
