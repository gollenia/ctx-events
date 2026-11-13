import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { formatCountdownLabel } from './countdown';
import { formatDateRange, formatTimeRange } from '@events/i18n';
import { useFeaturedEventData } from '../shared';
import type { FeaturedEventContext } from '../types';
import Inspector from './inspector';

type EditProps = {
	attributes: {
		displayMode?: string;
	};
	context?: FeaturedEventContext;
	setAttributes: (attributes: { displayMode: string }) => void;
};

export default function Edit({ attributes, context, setAttributes }: EditProps) {
	const { start, end } = useFeaturedEventData(context);
	const displayMode = attributes.displayMode ?? 'date-time';
	const [now, setNow] = useState(() => Date.now());

	useEffect(() => {
		if (displayMode !== 'countdown' || !start) return;
		setNow(Date.now());
		const timer = window.setInterval(() => setNow(Date.now()), 1000);
		return () => window.clearInterval(timer);
	}, [displayMode, start]);
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
					? formatCountdownLabel(start, now)
					: [date, time].filter(Boolean).join(', ');

	return (
		<>
			<Inspector attributes={attributes} setAttributes={setAttributes} />
			<p {...useBlockProps()}>{value}</p>
		</>
	);
}
