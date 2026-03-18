<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\UseCases\GetEventProgram;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use Contexis\Events\Shared\Presentation\RestRoute;

final class EventExportController implements RestController
{
	private RestRoute $route;

	public function __construct(
		private GetEventProgram $getEventProgram,
		private EventProgramPdfRenderer $pdfRenderer,
	) {
		$this->route = RestRoute::forType('events');
	}

	public function register(): void
	{
		$args = $this->route->getForCollection('/monthly-pdf');
		register_rest_route($args->namespace, $args->route, args: [[
			'methods' => 'GET',
			'callback' => [$this, 'downloadProgramPdf'],
			'permission_callback' => '__return_true',
			'args' => [
				'mode' => ['type' => 'string', 'default' => 'month'],
				'offset' => ['type' => 'integer', 'default' => 0],
				'show_empty_days' => ['type' => 'boolean', 'default' => true],
				'category' => ['type' => 'integer', 'required' => false],
			],
		]]);
	}

	public function downloadProgramPdf(\WP_REST_Request $request): \WP_REST_Response
	{
		$program = $this->getEventProgram->execute(
			(string) $request->get_param('mode'),
			(int) $request->get_param('offset'),
			$request->has_param('category') ? (int) $request->get_param('category') : null,
		);

		$this->pdfRenderer->download(
			$program,
			(bool) $request->get_param('show_empty_days'),
		);
	}
}
