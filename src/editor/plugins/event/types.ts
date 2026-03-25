export type EventMeta = {
	_booking_enabled?: boolean | number;
	_event_start?: string;
	_event_end?: string;
	_event_all_day?: boolean | number | string;
	_location_id?: number | string;
	_person_id?: number | string;
	_event_audience?: string;
	_event_start_date?: string;
	_event_end_date?: string;
	_event_start_time?: string;
	_event_end_time?: string;
	_recurrence_freq?: string;
	_recurrence_byday?: string;
	_recurrence_interval?: string | number;
	_recurrence_byweekno?: string;
};

export type EditorPost = {
	id?: number;
};

export type EditorSelection = {
	getCurrentPost: () => EditorPost | null;
	getCurrentPostType: () => string | null;
	isSavingPost?: () => boolean;
	isPublishingPost?: () => boolean;
	isPublishSidebarOpened?: () => boolean;
};

export type MediaOption = {
	value: number;
	label: string;
	media?: string;
};
