<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Presentation;

use Contexis\Events\Form\Application\DTOs\FormCriteria;
use Contexis\Events\Form\Application\UseCases\DeleteForm;
use Contexis\Events\Form\Application\UseCases\DuplicateForm;
use Contexis\Events\Form\Application\UseCases\ListForms;
use Contexis\Events\Form\Application\UseCases\SetFormStatus;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Infrastructure\FormPostTypes;
use Contexis\Events\Form\Presentation\Resources\FormListItemResource;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use WP_REST_Request;

final class FormController implements RestController
{

	public function __construct(
		private ListForms $listForms,
		private SetFormStatus $setFormStatus,
		private DuplicateForm $duplicateForm,
		private DeleteForm $deleteForm,
	)
	{
	}
	public function register(): void
	{
		register_rest_route('events/v3', '/forms', [
			'methods' => 'GET',
			'callback' => [$this, 'getForms'],
			'permission_callback' => function () {
				return current_user_can('edit_posts');
			},
			'args' => [
				'type' => [
					'type' => 'string',
					'enum' => ['booking', 'attendee', ''],
					'required' => false,
				],
				'search' => [
					'type' => 'string',
					'required' => false,
				],
				'page' => [
					'type' => 'integer',
					'required' => false,
					'default' => 0,
				],
				'per_page' => [
					'type' => 'integer',
					'required' => false,
					'default' => -1,
				],
				'status' => [
					'type' => 'array',
					'items' => [
						'type' => 'string',
						'enum' => ['publish', 'draft', 'trash'],
					],
					'required' => false,
				],
			],
		]);

		register_rest_route('events/v3', '/forms/(?P<id>\d+)/status', [
			'methods' => 'POST',
			'callback' => [$this, 'setStatus'],
			'args' => [
				'status' => [
					'type' => 'string',
					'enum' => ['publish', 'draft', 'trash'],
					'required' => true,
				],
			],
		]);

		register_rest_route('events/v3', '/forms/(?P<id>\d+)/duplicate', [
			'methods' => 'POST',
			'callback' => [$this, 'duplicate'],
			'permission_callback' => function () {		
				return current_user_can('edit_posts');
			},
		]);

		register_rest_route('events/v3', '/forms/(?P<id>\d+)', [
			'methods' => \WP_REST_Server::DELETABLE,
			'callback' => [$this, 'delete'],
			'permission_callback' => function () {		
				return current_user_can('edit_posts');
			},
		]);
	}

	public function getForms(\WP_REST_Request $request): \WP_REST_Response
	{
		$criteria = new FormCriteria(
			type: $request->get_param('type') ? FormType::from($request->get_param('type')) : null,
			search: $request->get_param('search'),
			page: (int) $request->get_param('page', 0),
			perPage: (int) $request->get_param('per_page', -1),
			orderBy: OrderBy::fromField($request->get_param('order_by') ?? 'date', Order::from($request->get_param('order') ?? 'desc')),
			status: $request->get_param('status') ? StatusList::fromStrings($request->get_param('status')) : null,
			tags: $request->get_param('tags') ?? [],
		);
		
		$page = $this->listForms->execute($criteria);

		$response = array_map(fn($item) => FormListItemResource::fromDTO($item), $page->toArray());

		$response = new \WP_REST_Response($response, 200);
        $response->header('X-WP-Total', $page->pagination()->totalItems);
        $response->header('X-WP-TotalPages', $page->pagination()->totalPages());
		if ($page->hasStatusCounts()) {
			$response->header('X-WP-StatusCounts', json_encode($page->statusCounts?->toArray()));
		}

		return $response;
	}

	public function setStatus(\WP_REST_Request $request): \WP_REST_Response
	{
		$id = (int) $request->get_param('id');
		$status = $request->get_param('status');
		$updated = $this->setFormStatus->execute(FormId::from($id), Status::from($status));
		if (!$updated) {
			return new \WP_REST_Response(['message' => 'Form not found'], 404);
		}

		return new \WP_REST_Response(['message' => 'Status updated'], 200);
	}

	public function duplicate(\WP_REST_Request $request): \WP_REST_Response
	{
		$id = (int) $request->get_param('id');
		$newFormId = $this->duplicateForm->execute(FormId::from($id));

		if ($newFormId === null) {
			return new \WP_REST_Response(['message' => 'Form not found'], 404);
		}

		return new \WP_REST_Response([
			'message' => 'Form duplicated',
			'id' => $newFormId->toInt(),
			'editUrl' => sprintf('/wp-admin/post.php?post=%d&action=edit', $newFormId->toInt()),
		], 200);
	}

	public function delete(\WP_REST_Request $request): \WP_REST_Response
	{
		$id = (int) $request->get_param('id');
		$deleted = $this->deleteForm->execute(FormId::from($id));
		return new \WP_REST_Response([
			'message' => $deleted ? 'Form deleted' : 'Form not found',
		], $deleted ? 200 : 404);
	}

}