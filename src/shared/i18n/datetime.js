import { __ } from '@wordpress/i18n';
import { getLocale } from './locale.js';

const parseDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return isNaN(d.getTime()) ? null : d;
};

/**
 * Formats two dates to a date range
 */
function formatDateRange(start, end = false) {
    const startDate = parseDate(start);
    const endDate = end ? parseDate(end) : startDate;

    if (!startDate || !endDate) return '';

    const options = {
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
function formatDate(date, format = undefined) {
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
function formatTime(date) {
    const dateObject = parseDate(date);
    if (!dateObject) return '';

    const options = {
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
function formatTimeRange(start, end = false) {
    const startDate = parseDate(start);
    const endDate = end ? parseDate(end) : startDate; 

    if (!startDate || !endDate) return '';

    const options = {
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