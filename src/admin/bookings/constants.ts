import { __ } from '@wordpress/i18n';

export const STATUS_LABELS: Record<number, string> = {
	1: __('Pending', 'ctx-events'),
	2: __('Approved', 'ctx-events'),
	3: __('Canceled', 'ctx-events'),
	4: __('Expired', 'ctx-events'),
};
