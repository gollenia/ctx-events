import type { ChangeEvent } from 'react';
import { useRef, useState } from '@wordpress/element';
import type { FieldValue } from '../types';

export type TextAreaProps = {
	label: string;
	placeholder: string;
	name: string;
	required: boolean;
	width: number;
	disabled: boolean;
	rows: number;
	formTouched?: boolean;
	customErrorMessage?: string;
	error?: string;
	onChange: (value: FieldValue) => void;
	value: string;
};

const TextArea = (props: TextAreaProps) => {
	const { label, placeholder, name, required, width, rows, disabled, onChange, customErrorMessage, error, value } = props;

	const textInputRef = useRef<HTMLTextAreaElement>(null);
	const [touched, setTouched] = useState(false);

	const onChangeHandler = (event: ChangeEvent<HTMLTextAreaElement>) => {
		onChange(event.target.value);
	};

	const isTouched = touched || props.formTouched;
	const hasError = !!error || (!textInputRef?.current?.validity.valid && isTouched);
	const errorMessage = error ?? customErrorMessage ?? textInputRef.current?.validationMessage;
	const errorId = `${name}-error`;

	const classes = [
		'ctx-form-field',
		'textarea',
		'input--width-' + width,
		required ? 'input--required' : '',
		hasError ? 'error' : '',
	].join(' ');

	return (
		<div className={classes} style={{ gridColumn: `span ${width}` }}>
			<label htmlFor={name}>{label}</label>
			<textarea
				id={name}
				name={name}
				required={required}
				aria-required={required}
				aria-invalid={hasError || undefined}
				aria-describedby={hasError && errorMessage ? errorId : undefined}
				disabled={disabled}
				rows={rows}
				onBlur={() => setTouched(true)}
				ref={textInputRef}
				placeholder={placeholder}
				onChange={onChangeHandler}
				value={value}
			/>
			{hasError && errorMessage && (
				<span id={errorId} role="alert" className="error-message">
					{errorMessage}
				</span>
			)}
		</div>
	);
};

TextArea.defaultProps = {
	label: '',
	placeholder: '',
	name: '',
	required: false,
	width: 6,
	rows: 3,
};

export default TextArea;
