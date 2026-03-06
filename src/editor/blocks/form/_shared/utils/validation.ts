const isValidSlug = (slug: string): boolean => {
	if (!slug) {
		return false;
	}
	const validPattern: RegExp = /^([a-zA-Z0-9_]){3,40}$/;
	return validPattern.test(slug);
};

const sanitizeSlug = (slug: string): string => {
	if (!slug) return '';
	return slug.toLowerCase().replace(/\s/g, '_');
};

const isValidLabel = (label: string | undefined): boolean => {
	return !!label && label.trim().length > 0;
};

const isSlugLocked = (slug: string): boolean => {
	return slug === 'first_name' || slug === 'last_name' || slug === 'email';
};

export { isValidSlug, sanitizeSlug, isValidLabel, isSlugLocked };
