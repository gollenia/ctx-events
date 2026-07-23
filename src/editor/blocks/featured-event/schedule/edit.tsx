import { useBlockProps } from '@wordpress/block-editor';
import { __, sprintf } from '@wordpress/i18n';
import { formatDateRange, formatTimeRange } from '@events/i18n';
import { useFeaturedEventData, type FeaturedEventContext } from '../shared';
import Inspector from './inspector';

type EditProps = {
	attributes: {
		displayMode?: string;
	};
	context?: FeaturedEventContext;
	setAttributes: (attributes: { displayMode: string }) => void;
};

const DAY_IN_MS = 24 * 60 * 60 * 1000;
const HOUR_IN_MS = 60 * 60 * 1000;
const MINUTE_IN_MS = 60 * 1000;

function formatCountdown(start: string): string {
	const startMs = new Date(start).getTime();
	const remainingMs = startMs - Date.now();

	if (Number.isNaN(startMs)) {
		return '';
	}

	if (remainingMs <= 0) {
		return __('Started', 'ctx-events');
	}

	if (remainingMs <= DAY_IN_MS) {
		const hours = Math.floor(remainingMs / HOUR_IN_MS);
		const minutes = Math.floor((remainingMs % HOUR_IN_MS) / MINUTE_IN_MS);

		if (hours <= 0) {
			return sprintf(__('in %d min', 'ctx-events'), Math.max(1, minutes));
		}

		return sprintf(__('in %d h %d min', 'ctx-events'), hours, minutes);
	}

	return sprintf(
		/* translators: %d: remaining days until the event starts */
		__('in %d days', 'ctx-events'),
		Math.ceil(remainingMs / DAY_IN_MS),
	);
}

export default function Edit({ attributes, context, setAttributes }: EditProps) {
	const { start, end } = useFeaturedEventData(context);
	const displayMode = attributes.displayMode ?? 'date-time';
	const placeholderValue =
		displayMode === 'date'
			? __('Event date will appear here.', 'ctx-events')
			: displayMode === 'time'
				? __('Event time will appear here.', 'ctx-events')
				: displayMode === 'countdown'
					? __('in 00:42 Seconds', 'ctx-events')
					: __('Event date and time will appear here.', 'ctx-events');

	if (!start) {
		return (
			<>
				<Inspector attributes={attributes} setAttributes={setAttributes} />
				<p {...useBlockProps()}>{placeholderValue}</p>
			</>
		);
	}

	const date = formatDateRange(start, end || start);
	const time = formatTimeRange(start, end || start);
	const value =
		displayMode === 'date'
			? date
			: displayMode === 'time'
				? time
				: displayMode === 'countdown'
					? formatCountdown(start)
					: [date, time].filter(Boolean).join(', ');

	return (
		<>
			<Inspector attributes={attributes} setAttributes={setAttributes} />
			<p {...useBlockProps()}>{value}</p>
		</>
	);
}
