import type { EventsTableEvent, EventsTableTerm } from './types';

type RawEvent = {
	id: number;
	url?: string | null;
	name: string;
	description?: string | null;
	startDate: string;
	endDate?: string | null;
	audience?: string | null;
	bookingSummary?: {
		available: number | null;
		denyReason: string | null;
	} | null;
	includes?: {
		location?: {
			id: number;
			name: string;
			address?: {
				addressLocality?: string | null;
				addressCountry?: string | null;
				addressRegion?: string | null;
			};
		} | null;
		person?: {
			id: number;
			honorificPrefix?: string | null;
			givenName?: string;
			familyName?: string;
			honorificSuffix?: string | null;
		} | null;
		categories?: EventsTableTerm[] | null;
		tags?: EventsTableTerm[] | null;
	} | null;
};

export function mapEventsTableEvent(event: RawEvent): EventsTableEvent {
	const categories = event.includes?.categories ?? [];
	const tags = event.includes?.tags ?? [];
	const location = event.includes?.location;
	const person = event.includes?.person;

	return {
		id: event.id,
		link: event.url ?? '#',
		title: event.name,
		excerpt: event.description ?? '',
		start: event.startDate,
		end: event.endDate ?? event.startDate,
		audience: event.audience ?? null,
		category: categories[0] ?? null,
		categories,
		tags,
		location: location
			? {
					id: location.id,
					name: location.name,
					city: location.address?.addressLocality ?? '',
					country: location.address?.addressCountry ?? '',
					state: location.address?.addressRegion ?? '',
				}
			: { id: 0, name: '', city: '', country: '', state: '' },
		bookings: event.bookingSummary
			? {
					hasBookings: true,
					spaces: event.bookingSummary.available,
					denyReason: event.bookingSummary.denyReason,
				}
			: null,
		person: person
			? {
					id: person.id,
					name: [
						person.honorificPrefix,
						person.givenName,
						person.familyName,
						person.honorificSuffix,
					]
						.map((part) => part?.trim())
						.filter(Boolean)
						.join(' '),
				}
			: null,
	};
}
