import type { VisibilityRule } from '../types';

// Normalize checkbox values: true/"checked"/"on"/"1"/1 → true, false/"unchecked"/""/null/0 → false
function normalizeValue(val: unknown): unknown {
	if (val === 'checked' || val === 'on' || val === '1' || val === 1) return true;
	if (val === 'unchecked' || val === 'off' || val === '0' || val === 0) return false;
	return val;
}

export function isRuleMet(rule: VisibilityRule, formData: Record<string, unknown>): boolean {
	const actual = normalizeValue(formData[rule.field] ?? null);
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
}

export function isFieldVisible(
	visibilityRule: VisibilityRule | null | undefined,
	formData: Record<string, unknown>,
): boolean {
	if (!visibilityRule) return true;
	return isRuleMet(visibilityRule, formData);
}
