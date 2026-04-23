import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { formatDateRange, formatTimeRange } from '@events/i18n';
import { useFeaturedEventData, type FeaturedEventContext } from '../shared';

type EditProps = {
	context?: FeaturedEventContext;
};

export default function Edit({ context }: EditProps) {
	const { start, end } = useFeaturedEventData(context);

	if (!start) {
		return (
			<p {...useBlockProps()}>
				{__('Event date and time will appear here.', 'ctx-events')}
			</p>
		);
	}

	const date = formatDateRange(start, end || start);
	const time = formatTimeRange(start, end || start);

	return (
		<p {...useBlockProps()}>
			{date}
			{time ? `, ${time}` : ''}
		</p>
	);
}
