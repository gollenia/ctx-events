import type { ChangeEvent } from 'react';
import { useState } from '@wordpress/element';
import type { FieldValue } from '../types';

export type RadioProps = {
	label: string;
	placeholder: string;
	name: string;
	required: boolean;
	width: number;
	options?: string[];
	disabled: boolean;
	onChange: (value: FieldValue) => void;
	value: string;
};

const Radio = (props: RadioProps) => {
	const { onChange, options, name, disabled, placeholder, width, required, value } = props;

	const [selection, setSelection] = useState(placeholder);

	const onChangeHandler = (event: ChangeEvent<HTMLInputElement>) => {
		setSelection(event.target.value);
		onChange(event.target.value);
	};

	const classes = [
		'ctx-form-field',
		'radio',
		'input--width-' + width,
		required ? 'select--required' : '',
	].join(' ');

	return (
		<div className={classes} style={{ gridColumn: `span ${width}` }}>
			<fieldset name={name}>
				<legend>{props.label}</legend>
				{options?.map((option, index) => {
					const optionId = `${name}-${index}`;
					return (
						<label key={option} htmlFor={optionId}>
							<input
								id={optionId}
								checked={selection === option}
								onChange={onChangeHandler}
								disabled={disabled}
								type="radio"
								value={option}
								name={name}
								required={required}
								aria-required={required}
							/>
							{option}
						</label>
					);
				})}
			</fieldset>
		</div>
	);
};

Radio.defaultProps = {
	label: '',
	placeholder: '',
	name: '',
	options: [],
	required: false,
	width: 6,
};

export default Radio;
