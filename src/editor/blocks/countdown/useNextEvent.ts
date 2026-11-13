import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type { CountdownAttributes } from './types';

type Event = { startDate: string };
type Result = { status: 'loading' | 'loaded' | 'error'; start: string };
const PAGE_SIZE = 100;

async function findNextEvent(attributes: CountdownAttributes): Promise<string> {
	const params = new URLSearchParams({
		per_page: String(PAGE_SIZE), order: 'ASC', order_by: 'date-time',
		scope: 'future', with_recurrences: 'true',
	});
	params.append('status[]', 'publish');
	if (attributes.categoryId) params.append('categories[]', String(attributes.categoryId));
	if (attributes.tagId) params.append('tags[]', String(attributes.tagId));
	if (attributes.locationId) params.set('location', String(attributes.locationId));

	// The recurrence endpoint has a 12-month window. If it is empty, still
	// look for ordinary events beyond that window.
	for (const withRecurrences of [true, false]) {
		params.set('with_recurrences', String(withRecurrences));
		for (let page = 1; ; page++) {
			params.set('page', String(page));
			const events = await apiFetch<Event[]>({ path: `/events/v3/events?${params.toString()}` });
			if (!Array.isArray(events)) throw new Error('Invalid event response');
			const next = events.find((event) => Date.parse(event.startDate) > Date.now());
			if (next) return next.startDate;
			if (events.length < PAGE_SIZE) break;
		}
	}
	return '';
}

export function useNextEvent(attributes: CountdownAttributes): Result {
	const { categoryId, tagId, locationId } = attributes;
	const [result, setResult] = useState<Result>({ status: 'loading', start: '' });
	const [revision, setRevision] = useState(0);

	useEffect(() => {
		let cancelled = false;
		setResult({ status: 'loading', start: '' });
		findNextEvent({ ...attributes, categoryId, tagId, locationId })
			.then((start) => { if (!cancelled) setResult({ status: 'loaded', start }); })
			.catch(() => { if (!cancelled) setResult({ status: 'error', start: '' }); });
		return () => { cancelled = true; };
	}, [categoryId, tagId, locationId, revision]);

	useEffect(() => {
		if (!result.start) return;
		const remaining = Date.parse(result.start) - Date.now();
		const delay = Math.min(Math.max(remaining + 1000, 1000), 2147483647);
		const timer = window.setTimeout(() => {
			if (Date.parse(result.start) <= Date.now()) setRevision((value) => value + 1);
			else setResult((value) => ({ ...value }));
		}, delay);
		return () => window.clearTimeout(timer);
	}, [result]);
	return result;
}
