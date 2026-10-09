export type RecurrenceFrequency = 'daily' | 'weekly' | 'monthly' | 'yearly';

export type RecurringEventListItem = {
	id: number;
	title: string;
	status: string;
	firstStartsAt: string | null;
	frequency: RecurrenceFrequency | null;
	endsOn: string | null;
};
