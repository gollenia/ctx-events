<?php

declare(strict_types=1);

return [
	[
		'core/paragraph',
		[
			'placeholder' => __('Describe the event...', 'ctx-events'),
		],
	],
	[
		'ctx-events/details',
		[],
		[
			['ctx-events/details-date', []],
			['ctx-events/details-time', []],
			['ctx-events/details-location', []],
		],
	],
];
