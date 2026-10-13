import type { DataFieldConfig } from '@events/datatable';
import { formatDate } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import StatusPill, { type StatusPillState } from '../../shared/status-pill';
import type { RecurringEventListItem } from './types';

const statusConfig: Record<string, { label: string; state: StatusPillState }> =
	{
		publish: { label: __('Published', 'ctx-events'), state: 'success' },
		future: { label: __('Scheduled', 'ctx-events'), state: 'info' },
		draft: { label: __('Draft', 'ctx-events'), state: 'warning' },
		pending: { label: __('Pending', 'ctx-events'), state: 'warning' },
		private: { label: __('Private', 'ctx-events'), state: 'info' },
		trash: { label: __('Trash', 'ctx-events'), state: 'error' },
	};

export const recurringEventFields: Array<DataFieldConfig> = [
	{
		id: 'title',
		label: __('Name', 'ctx-events'),
		enableSorting: true,
		render: (item: RecurringEventListItem) => (
			<strong>
				<a href={`/wp-admin/post.php?post=${item.id}&action=edit`}>
					{item.title || __('(No title)', 'ctx-events')}
				</a>
			</strong>
		),
	},
	{
		id: 'status',
		label: __('Status', 'ctx-events'),
		render: (item: RecurringEventListItem) => {
			const status = statusConfig[item.status] ?? {
				label: item.status,
				state: 'info' as const,
			};
			return <StatusPill state={status.state}>{status.label}</StatusPill>;
		},
	},
	{
		id: 'firstStartsAt',
		label: __('First occurrence', 'ctx-events'),
		enableSorting: true,
		getValue: (item: RecurringEventListItem) =>
			item.firstStartsAt
				? formatDate(item.firstStartsAt, {
						dateStyle: 'medium',
						timeStyle: 'short',
					})
				: '—',
	},
	{
		id: 'frequency',
		label: __('Frequency', 'ctx-events'),
		getValue: (item: RecurringEventListItem) => item.frequency ?? '—',
	},
	{
		id: 'endsOn',
		label: __('Ends on', 'ctx-events'),
		getValue: (item: RecurringEventListItem) =>
			item.endsOn ?? __('Open-ended', 'ctx-events'),
	},
];
