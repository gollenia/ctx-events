import type { ChangeEvent } from 'react';
import { useRef, useState } from '@wordpress/element';
import type { CountryRegion, FieldValue } from '../types';
import { getCountryOptions } from '../countries';

type CountryProps = {
	label: string;
	placeholder: string;
	name: string;
	required: boolean;
	width: number;
	region: CountryRegion;
	disabled: boolean;
	customError: string;
	help?: string;
	formTouched: boolean;
	customErrorMessage?: string;
	error?: string;
	onChange: (value: FieldValue) => void;
	value: string;
};

const browserLocale = navigator.language;

const Country = (props: CountryProps) => {
	const { onChange, disabled, required, name, label, width, region, help, customErrorMessage, error, value } = props;

	const inputRef = useRef<HTMLSelectElement>(null);
	const [touched, setTouched] = useState(false);

	const countries = getCountryOptions(region, browserLocale);

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
				disabled={disabled}
				onBlur={() => setTouched(true)}
				onChange={onChangeHandler}
				ref={inputRef}
				value={value}
			>
				<option value="" disabled>
					{help ?? 'Make a selection'}
				</option>
				{countries.map((country) => (
					<option key={country.value} value={country.value}>
						{country.label}
					</option>
				))}
			</select>
			{hasError && errorMessage && (
				<span id={errorId} role="alert" className="error-message">
					{errorMessage}
				</span>
			)}
		</div>
	);
};

Country.defaultProps = {
	label: '',
	placeholder: '',
	name: '',
	required: false,
	width: 6,
	region: 'world',
};

export default Country;
export type { CountryProps };
