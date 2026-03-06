import { __ } from '@wordpress/i18n';

type DateDiffResult = {
	total_days: number;
	years: number;
	months: number;
	days: number;
	result: string;
};

function dateDiff(date1: string, date2: string): DateDiffResult {
	const date1TimeStamp = new Date(date1).getTime();
	const date2TimeStamp = new Date(date2).getTime();

	const calc =
		date1TimeStamp > date2TimeStamp
			? new Date(date1TimeStamp - date2TimeStamp)
			: new Date(date2TimeStamp - date1TimeStamp);

	const calcFormat =
		`${calc.getDate()}-${calc.getMonth() + 1}-${calc.getFullYear()}`.split('-');

	const daysPassed = Number(Math.abs(Number(calcFormat[0])) - 1);
	const monthsPassed = Number(Math.abs(Number(calcFormat[1])) - 1);
	const yearsPassed = Number(Math.abs(Number(calcFormat[2])) - 1970);

	const totalDays = yearsPassed * 365 + monthsPassed * 30.417 + daysPassed;

	const result =
		(yearsPassed === 1
			? `${yearsPassed} ${__('year', 'ctx-events')} `
			: yearsPassed > 1
				? `${yearsPassed} ${__('years', 'ctx-events')} `
				: '') +
		(monthsPassed === 1
			? `${monthsPassed} ${__('month', 'ctx-events')} `
			: monthsPassed > 1
				? `${monthsPassed} ${__('months', 'ctx-events')} `
				: '') +
		(daysPassed === 1
			? `${daysPassed} ${__('day', 'ctx-events')}`
			: daysPassed > 1
				? `${daysPassed} ${__('days', 'ctx-events')}`
				: '');

	return {
		total_days: Math.round(totalDays),
		years: yearsPassed,
		months: monthsPassed,
		days: daysPassed,
		result: result.trim(),
	};
}

export default dateDiff;
