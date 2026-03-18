<?php

namespace Contexis\Events\Platform\Config;

use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use function DI\get;

return [
	get(AdminMenu::class),
	get(\Contexis\Events\Form\Presentation\FormAdmin::class),
];
