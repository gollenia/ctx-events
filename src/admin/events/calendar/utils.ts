import type { TimeScope } from '../../../types/types';

const startOfMonth = (date: Date): Date =>
	new Date(date.getFullYear(), date.getMonth(), 1);

export const getCreateEventUrl = (date: Date): string => {
	const year = date.getFullYear();
	const month = String(date.getMonth() + 1).padStart(2, '0');
	const day = String(date.getDate()).padStart(2, '0');

	return `/wp-admin/post-new.php?post_type=ctx-event&date=${year}-${month}-${day}`;
};

export const getMonthFromScope = (
	scope: TimeScope,
	events: Array<{ startDate: string }>,
): Date => {
	const now = new Date();

	switch (scope) {
		case 'next-month':
			return new Date(now.getFullYear(), now.getMonth() + 1, 1);
		case 'this-month':
		case 'today':
		case 'tomorrow':
		case 'one-week':
		case 'this-week':
		case 'future':
		case '1-months':
		case '2-months':
		case '3-months':
			return startOfMonth(now);
		default: {
			const firstEvent = events[0];
			return firstEvent ? startOfMonth(new Date(firstEvent.startDate)) : startOfMonth(now);
		}
	}
};
