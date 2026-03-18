<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress\Admin;

use Contexis\Events\Shared\Presentation\Contracts\AdminService;

interface AdminServiceInterface extends AdminService
{
	public function register(): void;
}
