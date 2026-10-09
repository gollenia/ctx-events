import type { DataViewConfig } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import type { RecurringEventListItem } from './types';

type WpRecurringEvent = { id: number; title: { rendered: string }; status: string; meta: Record<string, unknown> };

const stringMeta = (meta: Record<string, unknown>, key: string): string | null => {
	const value = meta[key];
	return typeof value === 'string' && value !== '' ? value : null;
};

export const useFetchRecurringEvents = (view: DataViewConfig) => {
	const [items, setItems] = useState<RecurringEventListItem[]>([]);
	const [isLoading, setIsLoading] = useState(false);
	const [pagination, setPagination] = useState({ totalItems: 0, totalPages: 0 });
	const query = useMemo(() => new URLSearchParams({
		context: 'edit',
		page: String(view.page),
		per_page: String(view.perPage),
		search: view.search ?? '',
		orderby: view.sort.field === 'title' ? 'title' : 'date',
		// Stored table preferences may predate the lower-case DataViewConfig type.
		// The WordPress REST schema accepts only `asc` and `desc`.
		order: view.sort.direction.toLowerCase(),
		_fields: 'id,title,status,meta',
	}).toString(), [view]);

	useEffect(() => {
		const load = async (): Promise<void> => {
			setIsLoading(true);
			try {
				const response = (await apiFetch({ path: `/wp/v2/ctx-event-recurring?${query}`, parse: false })) as Response;
				if (!response.ok) {
					const error = await response.json() as { message?: string };
					throw new Error(error.message ?? `Request failed with HTTP ${response.status}.`);
				}
				const posts = (await response.json()) as WpRecurringEvent[];
				setItems(posts.map((post) => ({ id: post.id, title: post.title.rendered, status: post.status, firstStartsAt: stringMeta(post.meta, '_recurrence_first_occurrence_starts_at'), frequency: stringMeta(post.meta, '_recurrence_frequency') as RecurringEventListItem['frequency'], endsOn: stringMeta(post.meta, '_recurrence_ends_on') })));
				setPagination({ totalItems: Number(response.headers.get('X-WP-Total') ?? 0), totalPages: Number(response.headers.get('X-WP-TotalPages') ?? 0) });
			} catch (error) {
				console.error('Could not load recurring events.', error);
				setItems([]);
				setPagination({ totalItems: 0, totalPages: 0 });
			} finally { setIsLoading(false); }
		};
		void load();
	}, [query, view.refreshKey]);
	return { items, isLoading, pagination };
};
