export type EventsTableAttributes = {
	showCategory: boolean;
	showLocation: '' | 'name' | 'city' | 'country' | 'state';
	tableShowHeader: boolean;
	tableShowDividers: boolean;
	tableAlternatingRows: boolean;
	tableHoverEffect: boolean;
	tableShowCalendarIcon: boolean;
	tableColumns?: string[];
	limit: number;
	order: 'asc' | 'desc';
	selectedCategory: number[];
	selectedLocation: number;
	selectedTags: number[];
	excerptLength: number;
	showAudience: boolean;
	showPerson?: '' | 'name' | 'image';
	showCategoryFilter: boolean;
	showTagFilter: boolean;
	showSearch: boolean;
	scope: string;
	bookedUpWarningThreshold: number;
	showBookedUp: boolean;
	includeRecurring: boolean;
	altText: string;
};

export type EventsTableTerm = {
	id: number;
	name: string;
};

export type EventsTableEvent = {
	id: number;
	link: string;
	title: string;
	excerpt: string;
	start: string;
	end: string;
	audience: string | null;
	category: EventsTableTerm | null;
	categories: EventsTableTerm[];
	tags: EventsTableTerm[];
	location: {
		id: number;
		name: string;
		city: string;
		country: string;
		state: string;
	};
	bookings: {
		hasBookings: boolean;
		spaces: number | null;
		denyReason: string | null;
	} | null;
	person: {
		id: number;
		name: string;
	} | null;
};
