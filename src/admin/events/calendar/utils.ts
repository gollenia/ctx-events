import { formatTime } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import type { TimeScope } from '../../../types/types';
import type { CalendarDay, CalendarEvent } from './types';

export const DAY_LABELS = [
	__('Mon', 'ctx-events'),
	__('Tue', 'ctx-events'),
	__('Wed', 'ctx-events'),
	__('Thu', 'ctx-events'),
	__('Fri', 'ctx-events'),
	__('Sat', 'ctx-events'),
	__('Sun', 'ctx-events'),
];

export const toDate = (value: string | null | undefined): Date | null => {
	if (!value) return null;
	const date = new Date(value);
	return Number.isNaN(date.getTime()) ? null : date;
};

export const startOfMonth = (date: Date): Date =>
	new Date(date.getFullYear(), date.getMonth(), 1);

const endOfMonth = (date: Date): Date =>
	new Date(date.getFullYear(), date.getMonth() + 1, 0);

const startOfCalendarGrid = (date: Date): Date => {
	const monthStart = startOfMonth(date);
	const day = monthStart.getDay();
	const diff = day === 0 ? 6 : day - 1;
	return new Date(
		monthStart.getFullYear(),
		monthStart.getMonth(),
		monthStart.getDate() - diff,
	);
};

export const isSameDay = (left: Date, right: Date): boolean =>
	left.getFullYear() === right.getFullYear() &&
	left.getMonth() === right.getMonth() &&
	left.getDate() === right.getDate();

const isSameMonth = (left: Date, right: Date): boolean =>
	left.getFullYear() === right.getFullYear() &&
	left.getMonth() === right.getMonth();

export const formatCalendarTime = (
	startDateString: string,
	endDateString: string | false = false,
): string => {
	const startDate = toDate(startDateString);
	const endDate = endDateString ? toDate(endDateString) : startDate;

	if (!startDate || !endDate) {
		return '';
	}

	const startTime = formatTime(startDateString);
	const endTime = formatTime(endDateString || startDateString);

	if (
		!endDateString ||
		startTime === endTime ||
		!isSameDay(startDate, endDate)
	) {
		return startTime;
	}

	return `${startTime} - ${endTime}`;
};

export const getCreateEventUrl = (date: Date): string => {
	const year = date.getFullYear();
	const month = String(date.getMonth() + 1).padStart(2, '0');
	const day = String(date.getDate()).padStart(2, '0');

	return `/wp-admin/post-new.php?post_type=ctx-event&date=${year}-${month}-${day}`;
};

const sortEvents = (events: Array<CalendarEvent>): Array<CalendarEvent> =>
	[...events].sort((left, right) => {
		const leftTime = toDate(left.startDate)?.getTime() ?? 0;
		const rightTime = toDate(right.startDate)?.getTime() ?? 0;
		return leftTime - rightTime;
	});

export const buildCalendarDays = (
	activeMonth: Date,
	events: Array<CalendarEvent>,
): Array<CalendarDay> => {
	const sortedEvents = sortEvents(events);
	const monthEnd = endOfMonth(activeMonth);
	const gridStart = startOfCalendarGrid(activeMonth);
	const days: Array<CalendarDay> = [];

	for (let index = 0; index < 42; index += 1) {
		const currentDate = new Date(
			gridStart.getFullYear(),
			gridStart.getMonth(),
			gridStart.getDate() + index,
		);

		if (
			currentDate > monthEnd &&
			currentDate.getDay() === 1 &&
			days.length >= 35
		) {
			break;
		}

		days.push({
			date: currentDate,
			key: currentDate.toISOString(),
			inMonth: isSameMonth(currentDate, activeMonth),
			events: sortedEvents.filter((event) => {
				const eventDate = toDate(event.startDate);
				return eventDate ? isSameDay(eventDate, currentDate) : false;
			}),
		});
	}

	return days;
};

const getInitialMonth = (events: Array<{ startDate: string }>): Date => {
	const firstEventDate = events
		.map((event) => toDate(event.startDate))
		.find((date): date is Date => date !== null);

	return firstEventDate
		? startOfMonth(firstEventDate)
		: startOfMonth(new Date());
};

export const getMonthFromScope = (
	scope: TimeScope,
	events: Array<{ startDate: string }>,
): Date => {
	const now = new Date();

	switch (scope) {
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
		case 'next-month':
			return new Date(now.getFullYear(), now.getMonth() + 1, 1);
		default:
			return getInitialMonth(events);
	}
};
