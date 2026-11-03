<?php

namespace Contexis\Events\Platform\Config;

use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Platform\Wordpress\Admin\ReactAdminNoticeSuppressor;
use function DI\get;

return [
	get(AdminMenu::class),
	get(ReactAdminNoticeSuppressor::class),
	get(\Contexis\Events\Form\Presentation\FormAdmin::class),
];
