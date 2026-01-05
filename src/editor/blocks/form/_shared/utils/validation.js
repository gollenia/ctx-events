import { __ } from '@wordpress/i18n';

/**
 * @param {string} slug 
 * @returns {boolean}
 */
const isValidSlug = (slug) => {
	if (!slug) {
		return false;
	}
	const validPattern = /^([a-zA-Z0-9_]){3,40}$/;
	return validPattern.test(slug);
};

/**
 * @param {string} slug 
 * @returns {string}
 */
const sanitizeSlug = (slug) => {
	if (!slug) return '';
    return slug.toLowerCase().replace(/\s/g, '_');
};

/**
 * @param {string} label 
 * @returns {boolean}
 */
const isValidLabel = (label) => {
    return label && label.trim().length > 0;
};

/**
 * @param {string} slug 
 * @returns {boolean}
 */
const isSlugLocked = (slug) => {
	return slug === 'first_name' || slug === 'last_name' || slug === 'email';
};

export { isValidSlug, sanitizeSlug, isValidLabel, isSlugLocked };