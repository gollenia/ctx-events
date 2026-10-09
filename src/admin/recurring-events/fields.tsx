import type { DataFieldConfig } from '@events/datatable';
import { __ } from '@wordpress/i18n';
import type { RecurringEventListItem } from './types';

export const recurringEventFields: Array<DataFieldConfig> = [
	{ id: 'title', label: __('Name', 'ctx-events'), enableSorting: true, render: (item: RecurringEventListItem) => <strong><a href={`/wp-admin/post.php?post=${item.id}&action=edit`}>{item.title || __('(No title)', 'ctx-events')}</a></strong> },
	{ id: 'firstStartsAt', label: __('First occurrence', 'ctx-events'), enableSorting: true, getValue: (item: RecurringEventListItem) => item.firstStartsAt ?? '—' },
	{ id: 'frequency', label: __('Frequency', 'ctx-events'), getValue: (item: RecurringEventListItem) => item.frequency ?? '—' },
	{ id: 'endsOn', label: __('Ends on', 'ctx-events'), getValue: (item: RecurringEventListItem) => item.endsOn ?? __('Open-ended', 'ctx-events') },
];
