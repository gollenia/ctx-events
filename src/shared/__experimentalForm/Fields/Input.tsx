import type { ChangeEvent, FormEvent, InvalidEvent } from 'react';
import { useRef, useState } from '@wordpress/element';
import type { FieldValue } from '../types';

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
	| 'number'
	| 'year';

type InputProps = {
	label: string;
	placeholder: string;
	name: string;
	required: boolean;
	autoComplete: string;
	pattern: string | null;
	width: number;
	disabled: boolean;
	customError: string;
	defaultValue: string;
	min?: number;
	max?: number;
	customErrorMessage?: string;
	error?: string;
	type: InputFieldTypes;
	help?: string;
	formTouched: boolean;
	onChange: (value: FieldValue) => void;
	value: string;
};

const TextInput = (props: InputProps) => {
	const [touched, setTouched] = useState(false);
	const inputRef = useRef<HTMLInputElement>(null);

	const { label, required, width, onChange, pattern, min, max, customErrorMessage, error, value, name } = props;

	const onChangeHandler = (event: ChangeEvent<HTMLInputElement>) => {
		onChange(event.target.value);
	};

	const onKeyPressHandler = (event: FormEvent<HTMLInputElement>) => {
		if (!pattern) return;
		const inputEvent = event.nativeEvent as InputEvent;
		const regex = new RegExp(pattern, 'gu');
		if (inputEvent.data !== null && !regex.test(inputEvent.data ?? '')) {
			event.preventDefault();
		}
	};

	const setInvalidity = (event: InvalidEvent<HTMLInputElement>) => {
		if (!props.customError) return;
		event.target.setCustomValidity(props.customError);
	};

	const isTouched = props.formTouched || touched;
	const hasError = !!error || (!inputRef?.current?.validity.valid && isTouched);
	const errorMessage = error ?? customErrorMessage ?? inputRef.current?.validationMessage;
	const errorId = `${name}-error`;

	const classes = [
		'ctx-form-field',
		'input',
		'input--width-' + width,
		required ? 'input--required' : '',
		hasError ? 'error' : '',
	].join(' ');

	const minMax = {
		minLength: min && props.type === 'text' ? min : undefined,
		maxLength: max && props.type === 'text' ? max : undefined,
		min: min && props.type === 'date' ? min : undefined,
		max: max && props.type === 'date' ? max : undefined,
	};

	return (
		<div className={classes} style={{ gridColumn: `span ${width}` }}>
			<label htmlFor={name}>{label}</label>
			<input
				{...minMax}
				id={name}
				name={name}
				placeholder={props.placeholder}
				required={required}
				aria-required={required}
				aria-invalid={hasError || undefined}
				aria-describedby={hasError && errorMessage ? errorId : undefined}
				onBlur={() => setTouched(true)}
				type={props.type}
				autoComplete={props.autoComplete}
				disabled={props.disabled}
				pattern={props.pattern ?? undefined}
				defaultValue={props.defaultValue}
				value={value}
				ref={inputRef}
				onInvalid={setInvalidity}
				onChange={onChangeHandler}
				onBeforeInput={onKeyPressHandler}
			/>
			{hasError && errorMessage && (
				<span id={errorId} role="alert" className="error-message">
					{errorMessage}
				</span>
			)}
		</div>
	);
};

TextInput.defaultProps = {
	label: '',
	placeholder: '',
	name: '',
	required: false,
	width: 6,
	type: 'text',
	pattern: null,
};

export default TextInput;
export type { InputFieldTypes, InputProps };
