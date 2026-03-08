import type { ChangeEvent } from 'react';
import { useRef, useState } from '@wordpress/element';
import type { FieldValue, SelectOptions } from '../types';

export type SelectProps = {
	label: string;
	placeholder: string;
	name: string;
	required: boolean;
	width: number;
	options?: SelectOptions;
	hasEmptyOption?: boolean;
	help?: string;
	hint?: string;
	disabled: boolean;
	multiple?: boolean;
	customError?: string;
	formTouched?: boolean;
	customErrorMessage?: string;
	error?: string;
	onChange: (value: FieldValue) => void;
	value: string;
};

const Select = (props: SelectProps) => {
	const {
		onChange, options, hasEmptyOption, help, hint, disabled, placeholder,
		multiple, required, label, name, customErrorMessage, error, width, value,
	} = props;

	const [touched, setTouched] = useState(false);
	const inputRef = useRef<HTMLSelectElement>(null);

	const onChangeHandler = (event: ChangeEvent<HTMLSelectElement>) => {
		onChange(event.target.value);
	};

	const isTouched = props.formTouched || touched;
	const hasError = !!error || (!inputRef?.current?.validity.valid && isTouched);
	const errorMessage = error ?? customErrorMessage ?? inputRef.current?.validationMessage;
	const errorId = `${name}-error`;

	const classes = [
		'ctx-form-field',
		'select',
		'input--width-' + width,
		required ? 'select--required' : '',
		hasError ? 'error' : '',
	].join(' ');

	const renderOptions = () => {
		if (!options) return null;
		if (Array.isArray(options)) {
			return options.map((option) => <option key={option}>{option}</option>);
		}
		return Object.entries(options).map(([key, optionLabel]) => (
			<option key={key} value={key}>{optionLabel}</option>
		));
	};

	return (
		<div className={classes} style={{ gridColumn: `span ${width}` }}>
			<label htmlFor={name}>{label}</label>
			<select
				id={name}
				name={name}
				required={required}
				aria-required={required}
				aria-invalid={hasError || undefined}
				aria-describedby={hasError && errorMessage ? errorId : undefined}
				onChange={onChangeHandler}
				onBlur={() => setTouched(true)}
				ref={inputRef}
				autoComplete={hint}
				disabled={disabled}
				multiple={multiple}
				defaultValue={placeholder}
				value={value}
			>
				{hasEmptyOption && (
					<option value="" disabled>
						{help ?? 'Make a selection'}
					</option>
				)}
				{renderOptions()}
			</select>
			{hasError && errorMessage && (
				<span id={errorId} role="alert" className="error-message">
					{errorMessage}
				</span>
			)}
		</div>
	);
};

Select.defaultProps = {
	label: '',
	placeholder: '',
	name: '',
	required: false,
	width: 6,
};

export default Select;
