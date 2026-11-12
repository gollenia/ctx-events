import type { FormField } from './types';

function eventDate(value: string): Date | null {
	const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(value);
	if (!match) return null;

	const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
	return Number.isNaN(date.getTime()) ? null : date;
}

function formatDate(date: Date): string {
	return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
}

function dateYearsBefore(date: Date, years: number): Date {
	return new Date(date.getFullYear() - years, date.getMonth(), date.getDate());
}

/** Returns form fields with birth-date bounds derived from the event date. */
export function withEventAgeLimits(
	fields: FormField[],
	eventStartDate: string,
	minimumAge: number | null,
	maximumAge: number | null,
): FormField[] {
	const startDate = eventDate(eventStartDate);
	if (!startDate) return fields;

	return fields.map((field) => {
		if (field.type !== 'date' || field.useEventAgeLimits !== true) return field;

		const limits: Partial<FormField> = {};
		if (minimumAge !== null) limits.max = formatDate(dateYearsBefore(startDate, minimumAge));
		if (maximumAge !== null) {
			const earliest = dateYearsBefore(startDate, maximumAge + 1);
			earliest.setDate(earliest.getDate() + 1);
			limits.min = formatDate(earliest);
		}

		return { ...field, ...limits };
	});
}
