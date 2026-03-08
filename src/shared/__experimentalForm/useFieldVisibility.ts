import type { VisibilityRule } from './types';

// Normalize checkbox values so both boolean true and string variants compare correctly.
const normalizeValue = (val: unknown): unknown => {
	if (val === 'checked' || val === 'on' || val === '1' || val === 1) return true;
	if (val === 'unchecked' || val === 'off' || val === '0' || val === 0) return false;
	return val;
};

const isRuleMet = (rule: VisibilityRule, form: Record<string, unknown>): boolean => {
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
	if (!visibilityRule) return true;
	return isRuleMet(visibilityRule, form);
};
