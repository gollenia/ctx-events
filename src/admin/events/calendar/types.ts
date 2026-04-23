export type CalendarEvent = {
	id: number;
	title: string;
	description: string;
	startDate: string;
	endDate: string;
	categoryIds: Array<number>;
	color: string | null;
	locationName: string | null;
	personName: string | null;
};

export type CalendarDay = {
	date: Date;
	key: string;
	inMonth: boolean;
	events: Array<CalendarEvent>;
};
