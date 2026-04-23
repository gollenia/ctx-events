import { ComboboxControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

type FeaturedEventAttributes = {
	selectedEvent: number;
};

type EventRecord = {
	id: number;
	title?: { raw?: string; rendered?: string };
};

type Props = {
	attributes: FeaturedEventAttributes;
	context?: {
		postId?: number;
		postType?: string;
	};
	label?: string;
	onChange: (attributes: Partial<FeaturedEventAttributes>) => void;
};

export default function EventSelector({
	attributes,
	context,
	label,
	onChange,
}: Props) {
	const eventOptions = useSelect((select) => {
		const list = ((select(coreStore) as {
			getEntityRecords: (
				kind: string,
				name: string,
				query?: Record<string, unknown>,
			) => EventRecord[] | null;
		}).getEntityRecords('postType', 'ctx-event', {
			per_page: -1,
			status: ['publish', 'future', 'draft', 'private'],
		}) ?? []) as EventRecord[];

		return [
			{
				label:
					context?.postType === 'ctx-event'
						? __('Current event', 'ctx-events')
						: __('Select an event', 'ctx-events'),
				value: '0',
			},
			...list.map((event) => ({
				label:
					event.title?.raw ||
					event.title?.rendered ||
					`${__('Event', 'ctx-events')} #${event.id}`,
				value: String(event.id),
			})),
		];
	}, [context?.postType]);

	return (
		<ComboboxControl
			label={label || __('Selected event', 'ctx-events')}
			value={String(attributes.selectedEvent ?? 0)}
			options={eventOptions}
			onChange={(value) =>
				onChange({ selectedEvent: value ? Number(value) : 0 })
			}
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	);
}
