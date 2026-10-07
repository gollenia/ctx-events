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
