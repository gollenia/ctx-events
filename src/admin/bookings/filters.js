import { getQueryArgs } from '@wordpress/url';
import { FIELD_IDS, STATUS } from './constants.js';

/** Baue DataViews-Filterobjekt */
export const makeFilter = (field, operator, value) => ({
	field,
	operator,
	value,
});

/** Defaults + URL -> initialer View  */
export function initialViewFromURL(baseView) {
	const q = getQueryArgs(window.location.href) || {};
	const filters = [];

	const hasStatus = q.status !== undefined || q['status[]'] !== undefined;
	if (!hasStatus) {
		filters.push(makeFilter(FIELD_IDS.STATUS, 'is', STATUS.PENDING));
	}

	if (q.event_id !== undefined) {
		if (Array.isArray(q.event_id)) {
			const ids = q.event_id.map(Number).filter(Number.isFinite);
			if (ids.length)
				filters.push(makeFilter(FIELD_IDS.EVENT_ID, 'isAny', ids));
		} else {
			const id = Number(q.event_id);
			if (Number.isFinite(id))
				filters.push(makeFilter(FIELD_IDS.EVENT_ID, 'is', id));
		}
	}

	return {
		...baseView,
		search: typeof q.search === 'string' ? q.search : baseView.search,
		filters,
	};
}

/** DataViews-View -> Query-Args für apiFetch */
export function viewToQuery(view) {
	const q = {};

	if (view.search) q.search = view.search;

	const { orderby, order } = view.sort || {};
	if (orderby) q.orderby = orderby;
	if (order) q.order = order;

	q.page = view.page || 1;
	q.per_page = view.perPage || 50;

	for (const f of view.filters || []) {
		const { field, operator, value } = f;

		if (field === FIELD_IDS.DATE) {
			const toISO = (v) => new Date(v).toISOString();
			if (operator === 'before') q.date_before = toISO(value);
			else if (operator === 'after') q.date_after = toISO(value);
			else if (operator === 'is') {
				const iso = toISO(value);
				q.date_gte = iso;
				q.date_lte = iso;
			}
			continue;
		}

		if (operator === 'is') q[field] = value;
		else if (operator === 'isAny' && Array.isArray(value))
			q[`${field}[]`] = value;
	}

	// saubere Param-Namen fürs Backend
	if (q['event.title']) {
		q.event_title = q['event.title'];
		delete q['event.title'];
	}
	if (q['event.title[]']) {
		q['event_title[]'] = q['event.title[]'];
		delete q['event.title[]'];
	}

	return q;
}
