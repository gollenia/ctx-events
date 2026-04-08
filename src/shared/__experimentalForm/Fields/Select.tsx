import { useId, useMemo, useRef, useState } from '@wordpress/element';
import type { ChangeEvent } from 'react';
import type { FieldValue, SelectOptions } from '../types';
import FieldShell from './FieldShell';

export type SelectProps = {
	label?: string;
	name?: string;
	required?: boolean;
	width?: number;
	options?: SelectOptions;
	hasEmptyOption?: boolean;
	help?: string;
	hint?: string;
	disabled?: boolean;
	customError?: string;
	formTouched?: boolean;
	customErrorMessage?: string;
	error?: string;
	onChange: (value: FieldValue) => void;
	value: string | string[];
	emptyOptionLabel?: string;
};

type NormalizedOption = {
	value: string;
	label: string;
};

const Select = (props: SelectProps) => {
	const {
		onChange,
		options,
		hasEmptyOption = false,
		help,
		hint,
		disabled = false,
		required = false,
		label = '',
		name = '',
		customError,
		customErrorMessage,
		error,
		width = 6,
		value,
		formTouched = false,
		emptyOptionLabel = 'Make a selection',
	} = props;

	const [touched, setTouched] = useState(false);
	const selectRef = useRef<HTMLSelectElement>(null);
	const reactId = useId();

	const selectId = name || `select-${reactId}`;
	const helpId = help ? `${selectId}-help` : undefined;
	const hintId = hint ? `${selectId}-hint` : undefined;
	const errorId = `${selectId}-error`;

	const isTouched = formTouched || touched;
	const nativeInvalid =
		!!selectRef.current && !selectRef.current.validity.valid;
	const hasError = !!error || (nativeInvalid && isTouched);
	const errorMessage =
		error ??
		customErrorMessage ??
		(isTouched ? selectRef.current?.validationMessage : undefined);

	const describedBy =
		[hintId, helpId, hasError && errorMessage ? errorId : undefined]
			.filter(Boolean)
			.join(' ') || undefined;

	const classes = [
		'ctx-form-field',
		'select',
		required ? 'select--required' : '',
		hasError ? 'error' : '',
	]
		.filter(Boolean)
		.join(' ');

	const normalizedOptions = useMemo<NormalizedOption[]>(() => {
		if (!options) return [];

		if (Array.isArray(options)) {
			return options.map((option) => ({
				value: option,
				label: option,
			}));
		}

		return Object.entries(options).map(([key, optionLabel]) => ({
			value: key,
			label: optionLabel,
		}));
	}, [options]);

	const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
		if (customError) {
			event.currentTarget.setCustomValidity('');
		}

		onChange(event.currentTarget.value);
	};

	const handleBlur = () => {
		setTouched(true);
	};

	const handleInvalid = (event: ChangeEvent<HTMLSelectElement>) => {
		if (customError) {
			event.currentTarget.setCustomValidity(customError);
		}
	};

	return (
		<FieldShell
			className={classes}
			label={label}
			required={required}
			labelFor={selectId}
			hint={hint}
			hintId={hintId}
			help={help}
			helpId={helpId}
			errorMessage={errorMessage}
			errorId={errorId}
			hasError={hasError}
		>
			<select
				ref={selectRef}
				id={selectId}
				name={name || undefined}
				required={required}
				disabled={disabled}
				value={value}
				aria-required={required || undefined}
				aria-invalid={hasError || undefined}
				aria-describedby={describedBy}
				aria-errormessage={hasError && errorMessage ? errorId : undefined}
				onChange={handleChange}
				onBlur={handleBlur}
				onInvalid={
					handleInvalid as unknown as React.FormEventHandler<HTMLSelectElement>
				}
			>
				{hasEmptyOption && (
					<option value="" disabled={required}>
						{emptyOptionLabel}
					</option>
				)}

				{normalizedOptions.map((option) => (
					<option key={option.value} value={option.value}>
						{option.label}
					</option>
				))}
			</select>
		</FieldShell>
	);
};

export default Select;
