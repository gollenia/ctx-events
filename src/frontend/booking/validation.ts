import { __ } from '@wordpress/i18n';
import { getBookingFieldDefinition } from './formFields';
import type { BookingFormData, FormField } from './types';
import { isFieldVisible } from './hooks/useFieldVisibility';

function isEmptyValue(value: unknown): boolean {
	return (
		value === undefined ||
		value === null ||
		value === '' ||
		value === false
	);
}

function getFallbackRequiredMessage(): string {
	return __('This field is required.', 'ctx-events');
}

function validateWithBrowserConstraints(
	field: FormField,
	value: unknown,
): string | null {
	if (typeof document === 'undefined') {
		return null;
	}

	const definition = getBookingFieldDefinition(field);
	const fieldType = definition.type;

	if (fieldType === 'html' || fieldType === 'hidden' || fieldType === 'submit') {
		return null;
	}

	if (fieldType === 'checkbox' || fieldType === 'toggle') {
		const input = document.createElement('input');
		input.type = 'checkbox';
		input.required = definition.required ?? false;
		input.checked = value === true;

		if (!input.checkValidity()) {
			return definition.customErrorMessage || input.validationMessage;
		}

		return null;
	}

	if (
		fieldType === 'select' ||
		fieldType === 'radio' ||
		fieldType === 'options'
	) {
		const select = document.createElement('select');
		select.required = definition.required ?? false;

		if (definition.hasEmptyOption !== false) {
			select.append(new Option('', ''));
		}

		const options = definition.options;
		if (Array.isArray(options)) {
			options.forEach((option) => select.append(new Option(option, option)));
		} else if (options) {
			Object.entries(options).forEach(([optionValue, optionLabel]) =>
				select.append(new Option(optionLabel, optionValue)),
			);
		}

		select.value = typeof value === 'string' ? value : '';

		if (!select.checkValidity()) {
			return definition.customErrorMessage || select.validationMessage;
		}

		return null;
	}

	if (fieldType === 'textarea') {
		const textarea = document.createElement('textarea');
		textarea.required = definition.required ?? false;
		textarea.value = typeof value === 'string' ? value : '';

		if (!textarea.checkValidity()) {
			return definition.customErrorMessage || textarea.validationMessage;
		}

		return null;
	}

	const input = document.createElement('input');
	input.type = fieldType === 'combobox' ? 'text' : fieldType;
	input.required = definition.required ?? false;

	if (definition.pattern) {
		input.pattern = definition.pattern;
	}

	if (definition.min !== undefined) {
		input.min = String(definition.min);
	}

	if (definition.max !== undefined) {
		input.max = String(definition.max);
	}

	input.value =
		typeof value === 'string' || typeof value === 'number' ? String(value) : '';

	if (!input.checkValidity()) {
		return definition.customErrorMessage || input.validationMessage;
	}

	return null;
}

export function validateField(
	field: FormField,
	formData: Record<string, unknown>,
): string | null {
	if (!isFieldVisible(field.visibilityRule, formData)) {
		return null;
	}

	const value = formData[field.name];

	if (field.required && isEmptyValue(value)) {
		return getFallbackRequiredMessage();
	}

	if (isEmptyValue(value)) {
		return null;
	}

	return validateWithBrowserConstraints(field, value);
}

export function isFieldComplete(
	field: FormField,
	formData: Record<string, unknown>,
): boolean {
	if (!isFieldVisible(field.visibilityRule, formData)) {
		return true;
	}

	if (!field.required) {
		return true;
	}

	return !isEmptyValue(formData[field.name]);
}

export function isBookingFormComplete(
	form: BookingFormData,
	formData: Record<string, unknown>,
): boolean {
	return form.fields.every((field) => isFieldComplete(field, formData));
}

export function validateBookingFormData(
	form: BookingFormData,
	formData: Record<string, unknown>,
): Record<string, string> {
	const errors: Record<string, string> = {};

	for (const field of form.fields) {
		const error = validateField(field, formData);

		if (error) {
			errors[field.name] = error;
		}
	}

	return errors;
}
