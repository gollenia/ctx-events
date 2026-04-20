import type {
	FieldValue,
	FormFieldDefinition,
	InputType,
} from '@contexis/wp-react-form';
import type { FormField } from './types';

export type BookingFormValues = Record<string, unknown>;

export const getBookingFieldInputType = (field: FormField): InputType => {
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
	field: FormField,
): FormFieldDefinition => {
	const definition: FormFieldDefinition = {
		name: field.name,
		label: field.label,
		description: field.description,
		type: getBookingFieldInputType(field),
		required: field.required,
		width: field.width,
		visibilityRule: field.visibilityRule,
		testId: `booking-field-${field.name}`,
		placeholder: typeof field.placeholder === 'string' ? field.placeholder : '',
		pattern: typeof field.pattern === 'string' ? field.pattern : null,
		min: typeof field.min === 'number' ? field.min : undefined,
		max: typeof field.max === 'number' ? field.max : undefined,
		content: typeof field.content === 'string' ? field.content : undefined,
		hasEmptyOption:
			typeof field.hasNullOption === 'boolean' ? field.hasNullOption : true,
	};

	if (Array.isArray(field.options)) {
		definition.options = Object.fromEntries(
			field.options
				.filter(
					(option): option is { value: string; label: string } =>
						typeof option?.value === 'string' &&
						typeof option?.label === 'string',
				)
				.map((option) => [option.value, option.label]),
		);
	}

	if (
		typeof field.defaultValue === 'string' ||
		typeof field.defaultValue === 'number' ||
		typeof field.defaultValue === 'boolean'
	) {
		definition.defaultValue = field.defaultValue;
	}

	if (field.type === 'checkbox' && typeof field.default === 'boolean') {
		definition.defaultValue = field.default;
	}

	return definition;
};

export const getBookingFieldInitialValue = (field: FormField): FieldValue => {
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

export const buildInitialFormValues = (
	fields: FormField[],
	initialValues: BookingFormValues,
): BookingFormValues => {
	const defaults = Object.fromEntries(
		fields.map((field) => [field.name, getBookingFieldInitialValue(field)]),
	);

	return {
		...defaults,
		...initialValues,
	};
};
