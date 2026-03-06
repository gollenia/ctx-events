import type { DataFieldConfig } from '@events/datatable';
import { __ } from '@wordpress/i18n';

export const fields: Array<DataFieldConfig> = [
	{
		id: 'title',
		label: __('Title', 'ctx-events'),
		enableSorting: true,
		render: (form) => (
			<strong>
				<a href={`/wp-admin/post.php?post=${form.id}&action=edit`}>
					{form.title || __('(No title)', 'ctx-events')}
				</a>
			</strong>
		),
	},
	{
		id: 'type',
		label: __('Type', 'ctx-events'),
		getValue: (form) =>
			form.type === 'booking'
				? __('Booking Form', 'ctx-events')
				: __('Attendee Form', 'ctx-events'),
		filterBy: {
			id: 'type',
			label: __('Type', 'ctx-events'),
			type: 'text',
			elements: [
				{ value: 'booking', label: __('Booking Form', 'ctx-events') },
				{ value: 'attendee', label: __('Attendee Form', 'ctx-events') },
			],
		},
	},
	{
		id: 'date',
		label: __('Date', 'ctx-events'),
		getValue: (form) =>
			new Date(form.createdAt).toLocaleDateString(undefined, {
				year: 'numeric',
				month: 'short',
				day: 'numeric',
			}),
		enableSorting: true,
	},
	{
		id: 'used',
		label: __('Used', 'ctx-events'),
		render: (form) => {
			if (form.usageCount) {
				return (
					<>
						{form.usageCount +
							(form.usageCount === 1
								? __(' time', 'ctx-events')
								: __(' times', 'ctx-events'))}
					</>
				);
			}
			return <>—</>;
		},
		enableSorting: true,
	},
	{
		id: 'description',
		label: __('Description', 'ctx-events'),
		getValue: (form) => form.description || '',
	},
];
