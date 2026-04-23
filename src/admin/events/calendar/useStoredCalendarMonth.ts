import { useCallback, useState } from '@wordpress/element';

const canUseStorage = (): boolean =>
	typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';

const serializeMonth = (date: Date): string =>
	`${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

const parseStoredMonth = (value: string | null): Date | null => {
	if (!value || !/^\d{4}-\d{2}$/.test(value)) {
		return null;
	}

	const [year, month] = value.split('-').map(Number);
	if (!year || !month) {
		return null;
	}

	return new Date(year, month - 1, 1);
};

const readStoredMonth = (storageKey: string, fallback: Date): Date => {
	if (!canUseStorage()) {
		return fallback;
	}

	return parseStoredMonth(window.localStorage.getItem(storageKey)) ?? fallback;
};

export const useStoredCalendarMonth = (
	storageKey: string,
	initialMonth: Date,
) => {
	const [activeMonth, setActiveMonthState] = useState<Date>(() =>
		readStoredMonth(storageKey, initialMonth),
	);

	const setActiveMonth = useCallback(
		(updater: Date | ((previousMonth: Date) => Date)) => {
			setActiveMonthState((previousMonth) => {
				const nextMonth =
					typeof updater === 'function' ? updater(previousMonth) : updater;

				if (canUseStorage()) {
					window.localStorage.setItem(storageKey, serializeMonth(nextMonth));
				}

				return nextMonth;
			});
		},
		[storageKey],
	);

	return { activeMonth, setActiveMonth };
};
