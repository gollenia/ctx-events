export type UpcomingAttributes = {
	showImages: boolean;
	roundImages?: boolean;
	imageSize?: number;
	showCategory: boolean;
	showLocation: '' | 'name' | 'city' | 'country' | 'state';
	filterPosition: 'top' | 'side';
	view: 'cards' | 'list' | 'mini';
	limit: number;
	order: 'asc' | 'desc';
	selectedCategory: number[];
	selectedLocation: number;
	selectedTags: number[];
	excerptLength: number;
	showAudience: boolean;
	showPerson?: '' | 'name' | 'image';
	textAlignment?: string;
	animateOnScroll?: boolean;
	animationType?: string;
	showCategoryFilter: boolean;
	showTagFilter: boolean;
	showSearch: boolean;
	userStylePicker: boolean;
	scope: string;
	bookedUpWarningThreshold: number;
	showBookedUp: boolean;
	excludeCurrent: boolean;
	altText: string;
};

export type UpcomingTerm = {
	id: number;
	name: string;
};

export type UpcomingViewEvent = {
	id: number;
	link: string;
	title: string;
	excerpt: string;
	start: string;
	end: string;
	audience: string | null;
	category: UpcomingTerm | null;
	categories: UpcomingTerm[];
	tags: UpcomingTerm[];
	location: {
		id: number;
		name: string;
		city: string;
		country: string;
		state: string;
	};
	image: {
		url: string;
		altText: string;
		sizes: Record<string, { url?: string }>;
	} | null;
	bookings: {
		hasBookings: boolean;
		spaces: number | null;
		isBookable: boolean;
		denyReason: string | null;
	} | null;
	person: {
		id: number;
		name: string;
	} | null;
};

export type UpcomingViewProps = {
	attributes: UpcomingAttributes;
	events: UpcomingViewEvent[];
	status: 'LOADING' | 'LOADED' | 'ERROR';
};
