<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation;

use Contexis\Events\Payment\Application\UseCases\EditGateway;
use Contexis\Events\Payment\Application\UseCases\ListGateways;
use Contexis\Events\Payment\Application\UseCases\ToggleGateway;
use Contexis\Events\Payment\Application\UseCases\UpdateGateway;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class GatewayController implements RestController
{
	/*
	 * TODO: Make this admin only
	 */
	public function __construct(
		private readonly EditGateway $editGateway,
		private readonly ListGateways $listGateways,
		private readonly ToggleGateway $toggleGateway,
		private readonly UpdateGateway $updateGateway,
	) {}
	public function register(): void
    {

		$base_args = [
            'slug' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'unique gateway identifier',
                'sanitize_callback' => 'sanitize_title'
            ]
        ];

		register_rest_route('events/v3', '/gateways', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'listGateways'],
            'permission_callback' => [$this, 'checkGatewayPermission'],
        ]);

		register_rest_route('events/v3', '/gateways/(?P<slug>\w+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'editGateway'],
                'permission_callback' => [$this, 'checkGatewayPermission'],
                'args'                => $base_args,
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'updateGateway'],
                'permission_callback' => [$this, 'checkGatewayPermission'],
                'args'                => array_merge($base_args, [
                    'settings' => ['required' => true, 'type' => 'object']
                ]),
            ],
            [
                'methods'             => 'PATCH',
                'callback'            => [$this, 'toggleGateway'],
                'permission_callback' => [$this, 'checkGatewayPermission'],
                'args'                => array_merge($base_args, [
                    'enabled' => ['required' => true, 'type' => 'boolean']
                ]),
            ],
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
		try {
			$result = $this->updateGateway->execute($request->get_param('slug'), $request->get_param('settings'));
			if(!$result) {
				return new \WP_REST_Response([], 400);
			}
			return new \WP_REST_Response($request->get_param('settings'));
		} catch (\DomainException $e) {
			return new \WP_REST_Response(['error'=> $e->getMessage()], 400);
		}
	}	

	public function toggleGateway(\WP_REST_Request $request): \WP_REST_Response
	{
		try {
			$result = $this->toggleGateway->execute($request->get_param('slug'), $request->get_param('enabled'));
			if(!$result) {
				return new \WP_REST_Response([], 400);
			}
			return new \WP_REST_Response($result);
		} catch (\DomainException $e) {	
			return new \WP_REST_Response(['error'=> $e->getMessage()], 400);
		}
	}

	public function checkGatewayPermission(): bool
	{
		return true;
		return current_user_can('manage_options');
	}
}
