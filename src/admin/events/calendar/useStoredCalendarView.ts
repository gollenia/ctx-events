import { useCallback, useState } from '@wordpress/element';

export type CalendarView = 'month-grid' | 'week' | 'year-grid';

const CALENDAR_VIEWS: Array<CalendarView> = ['month-grid', 'week', 'year-grid'];

const canUseStorage = (): boolean =>
	typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';

const isCalendarView = (value: string | null): value is CalendarView =>
	value !== null && CALENDAR_VIEWS.includes(value as CalendarView);

export const useStoredCalendarView = (storageKey: string) => {
	const [view, setViewState] = useState<CalendarView>(() => {
		if (!canUseStorage()) return 'month-grid';

		const storedView = window.localStorage.getItem(storageKey);
		return isCalendarView(storedView) ? storedView : 'month-grid';
	});

	const setView = useCallback(
		(nextView: CalendarView) => {
			if (canUseStorage()) window.localStorage.setItem(storageKey, nextView);
			setViewState(nextView);
		},
		[storageKey],
	);

	return { view, setView };
};
