import { __ } from '@wordpress/i18n';
import getLocale from './locale';

const parseDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return isNaN(d.getTime()) ? null : d;
};

const isInvalidDate = (dateString: string | null | undefined): boolean => {
    if (!dateString) return true;
    return dateString.startsWith('1970-01-01');
};

/**
 * Formats two dates to a date range
 */
function formatDateRange(start: string, end: string | false = false): string {

	if(isInvalidDate(start)) return __('Invalid Date', 'event-blocks');
	if (start === end) {
		return formatDate(start);
	}


    const startDate = parseDate(start);
    const endDate = end ? parseDate(end) : startDate;

    if (!startDate || !endDate) return '';

    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    };

    try {
        const formatter = new Intl.DateTimeFormat(getLocale(), options);
        return formatter.formatRange(startDate, endDate);
    } catch (e) {
        console.error('Date format error', e);
        return __('Invalid Date', 'event-blocks');
    }
}

/**
 * Format date by given format object
 */
function formatDate(date: string, format: Intl.DateTimeFormatOptions | undefined = undefined): string {
    const dateObject = parseDate(date);
    if (!dateObject) return '';

    const options = format || { year: 'numeric', month: 'long', day: 'numeric' };

    try {
        return new Intl.DateTimeFormat(getLocale(), options).format(dateObject);
    } catch (e) {
        return '';
    }
}

/**
 * Formats a simple time
 */
function formatTime(date: string): string {
    const dateObject = parseDate(date);
    if (!dateObject) return '';

    const options: Intl.DateTimeFormatOptions = {
        hour: 'numeric',
        minute: 'numeric',
    };

    try {
        return new Intl.DateTimeFormat(getLocale(), options).format(dateObject);
    } catch (e) {
        return __('Invalid Time', 'event-blocks');
    }
}

/**
 * Formats a time range (e.g. 10:00 – 12:00)
 */
function formatTimeRange(start: string, end: string | false = false): string {
    const startDate = parseDate(start);
    const endDate = end ? parseDate(end) : startDate; 

    if (!startDate || !endDate) return '';

    const options: Intl.DateTimeFormatOptions = {
        hour: 'numeric',
        minute: 'numeric',
    };

    try {
        const formatter = new Intl.DateTimeFormat(getLocale(), options);
        return formatter.formatRange(startDate, endDate);
    } catch (e) {
        return __('Invalid Time', 'event-blocks');
    }
}

export { formatDate, formatDateRange, formatTime, formatTimeRange };