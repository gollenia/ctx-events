import { __ } from '@wordpress/i18n';
import type {
	FieldValue,
	FormFieldDefinition,
	InputType,
} from '@contexis/wp-react-form';
import type { BookingFormField } from 'src/types/types';

export type BookingFormValues = Record<string, unknown>;

export const getFallbackRegistrationFields = (): BookingFormField[] => [
	{
		name: 'first_name',
		label: __('First Name', 'ctx-events'),
		required: true,
		width: 6,
		description: null,
		visibilityRule: null,
		type: 'input',
		inputType: 'text',
	},
	{
		name: 'last_name',
		label: __('Last Name', 'ctx-events'),
		required: true,
		width: 6,
		description: null,
		visibilityRule: null,
		type: 'input',
		inputType: 'text',
	},
	{
		name: 'email',
		label: __('E-Mail', 'ctx-events'),
		required: true,
		width: 6,
		description: null,
		visibilityRule: null,
		type: 'input',
		inputType: 'email',
	},
];

export const getBookingFieldInputType = (
	field: BookingFormField,
): InputType => {
	switch (field.type) {
		case 'input':
			return (field.inputType as InputType) ?? 'text';
		case 'date':
			return 'date';
		case 'number':
			return field.variant === 'slider' ? 'range' : 'number';
		case 'textarea':
			return 'textarea';
		case 'checkbox':
			return field.variant === 'switch' ? 'toggle' : 'checkbox';
		case 'select':
			if (field.selectVariant === 'radio') return 'radio';
			if (field.selectVariant === 'combobox') return 'combobox';
			return 'select';
		case 'country':
			return 'country';
		case 'html':
			return 'html';
		default:
			return 'text';
	}
};

export const getBookingFieldDefinition = (
	field: BookingFormField,
): FormFieldDefinition => {
	const definition: FormFieldDefinition = {
		name: field.name,
		label: field.label,
		type: getBookingFieldInputType(field),
		required: field.required,
		width: field.width,
		visibilityRule: field.visibilityRule,
		placeholder: field.placeholder ?? '',
		pattern: field.pattern ?? null,
		min: field.min,
		max: field.max,
		content: field.content,
		hasEmptyOption: field.hasNullOption ?? true,
	};

	if (Array.isArray(field.options)) {
		definition.options = Object.fromEntries(
			field.options.map((option) => [option.value, option.label]),
		);
	}

	if (field.defaultValue !== undefined && field.defaultValue !== null) {
		if (
			typeof field.defaultValue === 'string' ||
			typeof field.defaultValue === 'number' ||
			typeof field.defaultValue === 'boolean'
		) {
			definition.defaultValue = field.defaultValue;
		}
	}

	return definition;
};

export const getBookingFieldInitialValue = (
	field: BookingFormField,
): FieldValue => {
	if (typeof field.defaultValue === 'string') return field.defaultValue;
	if (typeof field.defaultValue === 'number') return field.defaultValue;
	if (typeof field.defaultValue === 'boolean') return field.defaultValue;
	if (field.type === 'checkbox') {
		if (typeof field.default === 'boolean') return field.default;
		return false;
	}
	if (field.type === 'number') return 0;
	return '';
};

export const getBookingFieldValue = (
	field: BookingFormField,
	values: BookingFormValues,
): FieldValue => {
	const rawValue = values[field.name];

	if (field.type === 'checkbox') {
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
		return getBookingFieldInitialValue(field);
	}

	if (field.type === 'number') {
		if (typeof rawValue === 'number') return rawValue;
		if (typeof rawValue === 'string' && rawValue.trim() !== '') {
			const parsedValue = Number(rawValue);
			if (!Number.isNaN(parsedValue)) {
				return parsedValue;
			}
		}
		return getBookingFieldInitialValue(field);
	}

	if (typeof rawValue === 'string') return rawValue;
	if (typeof rawValue === 'number') return String(rawValue);
	if (typeof rawValue === 'boolean') return rawValue ? '1' : '0';
	const initialValue = getBookingFieldInitialValue(field);
	return typeof initialValue === 'number' ? String(initialValue) : initialValue;
};
