import type { UpcomingTerm, UpcomingViewEvent } from './types';

type RawImage = {
	url?: string;
	alt_text?: string;
	sizes?: Record<string, { url?: string }>;
};

type RawLocation = {
	id: number;
	name: string;
	address?: {
		addressLocality?: string | null;
		addressCountry?: string | null;
		addressRegion?: string | null;
	};
};

type RawIncludes = {
	image?: RawImage | null;
	location?: RawLocation | null;
	person?: {
		id: number;
		givenName?: string;
		familyName?: string;
	} | null;
	categories?: UpcomingTerm[] | null;
	tags?: UpcomingTerm[] | null;
};

type RawEvent = Event & {
	includes?: RawIncludes | null;
};

export function mapUpcomingEvent(event: RawEvent): UpcomingViewEvent {
	const categories = event.includes?.categories ?? [];
	const tags = event.includes?.tags ?? [];
	const location = event.includes?.location;
	const image = event.includes?.image;
	const person = event.includes?.person;

	return {
		id: event.id,
		link: event.schema?.id ?? '#',
		title: event.name,
		excerpt: event.description ?? '',
		start: event.startDate,
		end: event.endDate ?? event.startDate,
		audience: event.audience,
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
		image: image
			? {
					url: image.url ?? '',
					altText: image.alt_text ?? '',
					sizes: image.sizes ?? {},
				}
			: null,
		bookings: event.bookingSummary
			? {
					hasBookings: true,
					spaces: event.bookingSummary.available,
					isBookable: event.bookingSummary.isBookable,
				}
			: null,
		person: person
			? {
					id: person.id,
					name: [person.givenName, person.familyName]
						.filter(Boolean)
						.join(' '),
				}
			: null,
	};
}
