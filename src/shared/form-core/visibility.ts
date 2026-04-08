import type { VisibilityRule } from './types';

// Normalize checkbox values so both boolean true and string variants compare correctly.
const normalizeValue = (value: unknown): unknown => {
	if (value === 'checked' || value === 'on' || value === '1' || value === 1) {
		return true;
	}

	if (value === 'unchecked' || value === 'off' || value === '0' || value === 0) {
		return false;
	}

	return value;
};

const isRuleMet = (
	rule: VisibilityRule,
	form: Record<string, unknown>,
): boolean => {
	const actual = normalizeValue(form[rule.field] ?? null);
	const expected = normalizeValue(rule.value);

	switch (rule.operator) {
		case 'equals':
			// eslint-disable-next-line eqeqeq
			return actual == expected;
		case 'not_equals':
			// eslint-disable-next-line eqeqeq
			return actual != expected;
		case 'not_empty':
			return actual !== null && actual !== '' && actual !== false;
		default:
			return false;
	}
};

export const isFieldVisible = (
	visibilityRule: VisibilityRule | null | undefined,
	form: Record<string, unknown>,
): boolean => {
	if (!visibilityRule) {
		return true;
	}

	return isRuleMet(visibilityRule, form);
};
