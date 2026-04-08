import type { FieldValue, FormFieldDefinition, FormValues } from './types';

export const getDefaultFormValues = (
	fields: FormFieldDefinition[],
): FormValues => {
	const values: FormValues = {};

	fields.forEach((field) => {
		if (field.defaultValue !== undefined) {
			values[field.name] = field.defaultValue;
		}
	});

	return values;
};

export const normalizeFieldValue = (
	field: FormFieldDefinition,
	formData: Record<string, unknown>,
): FieldValue => {
	const rawValue = formData[field.name];

	switch (field.type) {
		case 'checkbox':
		case 'toggle':
			if (typeof rawValue === 'boolean') return rawValue;
			if (
				rawValue === 'checked' ||
				rawValue === 'on' ||
				rawValue === '1' ||
				rawValue === 1
			) {
				return true;
			}
			if (
				rawValue === 'unchecked' ||
				rawValue === 'off' ||
				rawValue === '0' ||
				rawValue === 0
			) {
				return false;
			}
			return typeof field.defaultValue === 'boolean'
				? field.defaultValue
				: false;

		case 'number':
		case 'range':
		case 'numberpicker':
			if (typeof rawValue === 'number') return String(rawValue);
			if (typeof rawValue === 'string') return rawValue;
			if (typeof field.defaultValue === 'number') return String(field.defaultValue);
			if (typeof field.defaultValue === 'string') return field.defaultValue;
			return '';

		default:
			if (typeof rawValue === 'string') return rawValue;
			if (typeof rawValue === 'number') return String(rawValue);
			if (typeof rawValue === 'boolean') return rawValue ? '1' : '0';
			if (typeof field.defaultValue === 'string') return field.defaultValue;
			if (typeof field.defaultValue === 'number') return String(field.defaultValue);
			if (typeof field.defaultValue === 'boolean') return field.defaultValue;
			return '';
	}
};
