import { useId, useRef, useState } from '@wordpress/element';
import type { ChangeEvent } from 'react';
import type { FieldValue } from '../types';
import FieldShell from './FieldShell';

export type TextAreaProps = {
	label?: string;
	placeholder?: string;
	name?: string;
	required?: boolean;
	width?: number;
	disabled?: boolean;
	rows?: number;
	help?: string;
	formTouched?: boolean;
	customErrorMessage?: string;
	error?: string;
	onChange: (value: FieldValue) => void;
	value: string;
};

const TextArea = (props: TextAreaProps) => {
	const {
		label = '',
		placeholder = '',
		name = '',
		required = false,
		width = 6,
		disabled = false,
		rows = 3,
		help,
		formTouched = false,
		customErrorMessage,
		error,
		onChange,
		value,
	} = props;

	const textAreaRef = useRef<HTMLTextAreaElement>(null);
	const [touched, setTouched] = useState(false);
	const reactId = useId();

	const textAreaId = name || `textarea-${reactId}`;
	const helpId = help ? `${textAreaId}-help` : undefined;
	const errorId = `${textAreaId}-error`;

	const isTouched = touched || formTouched;
	const nativeInvalid =
		!!textAreaRef.current && !textAreaRef.current.validity.valid;
	const hasError = !!error || (nativeInvalid && isTouched);
	const errorMessage =
		error ??
		customErrorMessage ??
		(isTouched ? textAreaRef.current?.validationMessage : undefined);

	const describedBy =
		[helpId, hasError && errorMessage ? errorId : undefined]
			.filter(Boolean)
			.join(' ') || undefined;

	const classes = [
		'ctx-form-field',
		'textarea',
		required ? 'input--required' : '',
		hasError ? 'error' : '',
	]
		.filter(Boolean)
		.join(' ');

	const handleChange = (event: ChangeEvent<HTMLTextAreaElement>) => {
		onChange(event.currentTarget.value);
	};

	return (
		<FieldShell
			className={classes}
			label={label}
			required={required}
			labelFor={textAreaId}
			help={help}
			helpId={helpId}
			errorMessage={errorMessage}
			errorId={errorId}
			hasError={hasError}
		>
			<textarea
				ref={textAreaRef}
				id={textAreaId}
				name={name || undefined}
				required={required}
				disabled={disabled}
				rows={rows}
				placeholder={placeholder}
				value={value}
				aria-required={required || undefined}
				aria-invalid={hasError || undefined}
				aria-describedby={describedBy}
				aria-errormessage={hasError && errorMessage ? errorId : undefined}
				onBlur={() => setTouched(true)}
				onChange={handleChange}
			/>
		</FieldShell>
	);
};

export default TextArea;
