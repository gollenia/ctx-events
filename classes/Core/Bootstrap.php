<?php

namespace Contexis\Events\Core;

use Contexis\Events\Emails\Mailer;
use Contexis\Events\Notices;
use Contexis\Events\Payment\GatewayService;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;


class Bootstrap {

	use \Contexis\Events\Core\Contracts\Application;

	public static function init(): void {
		$instance = new self();
		$symfonyRequest = SymfonyRequest::createFromGlobals();
		$typedRequest = new Request($symfonyRequest);

		$instance->app()->bind(Request::class, $typedRequest);
		$instance->app()->bind(SymfonyRequest::class, $symfonyRequest);

		$instance->app()->bind(GatewayService::class, function() {
			return new GatewayService();
		});

		$instance->app()->bind(Mailer::class, new Mailer());
		$instance->app()->bind(Notices::class, new Notices());
		
	}
}