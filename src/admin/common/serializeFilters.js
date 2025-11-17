const serializeFilters = (view) => {
	const q = {};
	// Suche
	if (view?.search) q.search = view.search;

	// Sortierung
	if (view?.sort?.orderby) q.orderby = view.sort.orderby;
	if (view?.sort?.order) q.order = view.sort.order;

	// Pagination
	if (view?.page) q.page = view.page;
	if (view?.perPage) q.per_page = view.perPage;

	// Filter
	for (const f of view?.filters || []) {
		const field = f.field; // z.B. 'status', 'gateway', 'event.title', 'date'
		const op = f.operator; // 'is' | 'isAny' | 'before' | 'after'
		const val = f.value;

		if (field === 'date') {
			const iso = (v) => new Date(v).toISOString();
			if (op === 'before') q.date_before = iso(val);
			if (op === 'after') q.date_after = iso(val);
			if (op === 'is') {
				q.date_gte = iso(val);
				q.date_lte = iso(val);
			}
			continue;
		}

		if (op === 'is') q[field] = String(val);
		if (op === 'isAny' && Array.isArray(val)) q[`${field}[]`] = val;
	}

	// Mapping optional: 'event.title' -> 'event_title'
	if (q['event.title']) {
		q.event_title = q['event.title'];
		delete q['event.title'];
	}
	if (q['event.title[]']) {
		q['event_title[]'] = q['event.title[]'];
		delete q['event.title[]'];
	}

	return q;
};

export default serializeFilters;
