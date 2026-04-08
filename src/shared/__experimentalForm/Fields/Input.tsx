import { useId, useMemo, useRef, useState } from '@wordpress/element';
import type { ChangeEvent, FormEvent, InvalidEvent } from 'react';
import type { FieldValue } from '../types';
import FieldShell from './FieldShell';

type InputFieldTypes =
	| 'text'
	| 'email'
	| 'url'
	| 'color'
	| 'tel'
	| 'password'
	| 'search'
	| 'datetime-local'
	| 'date'
	| 'week'
	| 'month'
	| 'number';

type InputProps = {
	label?: string;
	placeholder?: string;
	name?: string;
	required?: boolean;
	autoComplete?: string;
	pattern?: string | null;
	width?: number;
	disabled?: boolean;
	customError?: string;
	min?: number | string;
	max?: number | string;
	customErrorMessage?: string;
	error?: string;
	type?: InputFieldTypes;
	help?: string;
	formTouched?: boolean;
	onChange: (value: FieldValue) => void;
	value: string;
};

const TextInput = (props: InputProps) => {
	const {
		label = '',
		placeholder = '',
		name = '',
		required = false,
		autoComplete = '',
		pattern = null,
		width = 6,
		disabled = false,
		customError = '',
		min,
		max,
		customErrorMessage,
		error,
		type = 'text',
		help,
		formTouched = false,
		onChange,
		value,
	} = props;

	const [touched, setTouched] = useState(false);
	const inputRef = useRef<HTMLInputElement>(null);
	const reactId = useId();

	const inputId = name || `input-${reactId}`;
	const helpId = help ? `${inputId}-help` : undefined;
	const errorId = `${inputId}-error`;

	const isTouched = formTouched || touched;
	const nativeInvalid = !!inputRef.current && !inputRef.current.validity.valid;
	const hasError = !!error || (nativeInvalid && isTouched);

	const errorMessage =
		error ??
		customErrorMessage ??
		(isTouched ? inputRef.current?.validationMessage : undefined);

	const describedBy =
		[helpId, hasError && errorMessage ? errorId : undefined]
			.filter(Boolean)
			.join(' ') || undefined;

	const classes = [
		'ctx-form-field',
		'input',
		required ? 'input--required' : '',
		hasError ? 'error' : '',
	]
		.filter(Boolean)
		.join(' ');

	const inputConstraints = useMemo(() => {
		const constraints: Record<string, number | string | undefined> = {};

		if (
			type === 'text' ||
			type === 'email' ||
			type === 'password' ||
			type === 'search' ||
			type === 'tel' ||
			type === 'url'
		) {
			if (typeof min === 'number') constraints.minLength = min;
			if (typeof max === 'number') constraints.maxLength = max;
		}

		if (
			type === 'number' ||
			type === 'date' ||
			type === 'datetime-local' ||
			type === 'week' ||
			type === 'month'
		) {
			if (min !== undefined) constraints.min = min;
			if (max !== undefined) constraints.max = max;
		}

		return constraints;
	}, [min, max, type]);

	const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
		if (customError) {
			event.currentTarget.setCustomValidity('');
		}
		onChange(event.target.value);
	};

	const handleBlur = () => {
		setTouched(true);
	};

	const handleInvalid = (event: InvalidEvent<HTMLInputElement>) => {
		if (customError) {
			event.currentTarget.setCustomValidity(customError);
		}
	};

	const handleBeforeInput = (event: FormEvent<HTMLInputElement>) => {
		if (!pattern) return;

		const nativeEvent = event.nativeEvent as InputEvent;
		if (nativeEvent.data == null) return;

		try {
			const regex = new RegExp(pattern, 'u');
			if (!regex.test(nativeEvent.data)) {
				event.preventDefault();
			}
		} catch {
			// Invalid regex pattern: ignore silently to avoid breaking input.
		}
	};

	return (
		<FieldShell
			className={classes}
			label={label}
			required={required}
			labelFor={inputId}
			help={help}
			helpId={helpId}
			errorMessage={errorMessage}
			errorId={errorId}
			hasError={hasError}
		>
			<input
				{...inputConstraints}
				ref={inputRef}
				id={inputId}
				name={name || undefined}
				type={type}
				value={value}
				placeholder={placeholder}
				required={required}
				disabled={disabled}
				autoComplete={autoComplete || undefined}
				pattern={pattern ?? undefined}
				aria-required={required || undefined}
				aria-invalid={hasError || undefined}
				aria-describedby={describedBy}
				aria-errormessage={hasError && errorMessage ? errorId : undefined}
				onChange={handleChange}
				onBlur={handleBlur}
				onInvalid={handleInvalid}
				onBeforeInput={handleBeforeInput}
			/>
		</FieldShell>
	);
};

export default TextInput;
export type { InputFieldTypes, InputProps };
