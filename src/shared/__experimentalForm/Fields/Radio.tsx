import { useId } from '@wordpress/element';
import type { ChangeEvent } from 'react';
import type { FieldValue, SelectOptions } from '../types';
import { FieldMessages } from './FieldShell';

export type RadioProps = {
	label?: string;
	name?: string;
	required?: boolean;
	width?: number;
	options?: SelectOptions;
	disabled?: boolean;
	help?: string;
	error?: string;
	customErrorMessage?: string;
	formTouched?: boolean;
	onChange: (value: FieldValue) => void;
	value: string;
};

const Radio = (props: RadioProps) => {
	const {
		onChange,
		options = [],
		name = '',
		disabled = false,
		width = 6,
		required = false,
		label = '',
		help,
		error,
		customErrorMessage,
		value,
	} = props;

	const reactId = useId();
	const fieldId = name || `radio-${reactId}`;
	const helpId = help ? `${fieldId}-help` : undefined;
	const errorId = `${fieldId}-error`;

	const hasError = !!error;
	const errorMessage = error ?? customErrorMessage;
	const describedBy =
		[helpId, hasError && errorMessage ? errorId : undefined]
			.filter(Boolean)
			.join(' ') || undefined;

	const classes = [
		'ctx-form-field',
		'radio',
		required ? 'radio--required' : '',
		hasError ? 'error' : '',
	]
		.filter(Boolean)
		.join(' ');

	const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
		onChange(event.currentTarget.value);
	};

	return (
		<div className={classes}>
			<fieldset
				className="ctx-radio-group"
				aria-describedby={describedBy}
				aria-invalid={hasError || undefined}
			>
				{label && (
					<legend>
						<span>{label}</span>
						{required && (
							<span
								className="ctx-form-label__required"
								aria-hidden="true"
							/>
						)}
					</legend>
				)}

				{(Array.isArray(options)
					? options.map((option) => ({
							value: option,
							label: option,
						}))
					: Object.entries(options ?? {}).map(([optionValue, optionLabel]) => ({
							value: optionValue,
							label: optionLabel,
						}))).map((option, index) => {
					const optionId = `${fieldId}-${index}`;

					return (
						<label key={optionId} htmlFor={optionId}>
							<input
								id={optionId}
								type="radio"
								name={name || fieldId}
								value={option.value}
								checked={value === option.value}
								onChange={handleChange}
								disabled={disabled}
								required={required}
							/>
							<span>{option.label}</span>
						</label>
					);
				})}
			</fieldset>

			<FieldMessages
				help={help}
				helpId={helpId}
				errorMessage={errorMessage}
				errorId={errorId}
				hasError={hasError}
			/>
		</div>
	);
};

export default Radio;
