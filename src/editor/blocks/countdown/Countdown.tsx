import { useEffect, useState } from '@wordpress/element';
import { __, _n } from '@wordpress/i18n';
import { formatDate } from '@events/i18n';
import type { CountdownAttributes } from './types';
import { useNextEvent } from './useNextEvent';

function LiveCountdown({ start }: { start: string }) {
	const [now, setNow] = useState(() => Date.now());
	useEffect(() => {
		setNow(Date.now());
		const timer = window.setInterval(() => setNow(Date.now()), 1000);
		return () => window.clearInterval(timer);
	}, [start]);
	const seconds = Math.max(0, Math.ceil((Date.parse(start) - now) / 1000));
	const days = Math.floor(seconds / 86400);
	const hours = Math.floor((seconds % 86400) / 3600);
	const minutes = Math.floor((seconds % 3600) / 60);
	const rest = seconds % 60;
	const pad = (value: number) => String(value).padStart(2, '0');
	return <span>{days} {_n('day', 'days', days, 'ctx-events')} {pad(hours)}:{pad(minutes)}:{pad(rest)}</span>;
}

export default function Countdown({ attributes }: { attributes: CountdownAttributes }) {
	const result = useNextEvent(attributes);
	if (result.status === 'loading') return <span>{__('Loading…', 'ctx-events')}</span>;
	if (result.status === 'error') return <span>{__('The event search failed.', 'ctx-events')}</span>;
	if (!result.start) return <span>{__('No matching events found.', 'ctx-events')}</span>;
	return attributes.displayMode === 'date'
		? <time dateTime={result.start}>{formatDate(result.start)}</time>
		: <LiveCountdown start={result.start} />;
}
