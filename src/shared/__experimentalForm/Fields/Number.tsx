import type { ChangeEvent } from 'react';
import { useRef } from '@wordpress/element';
import type { FieldValue } from '../types';

export type NumberInputProps = {
	label: string;
	placeholder: string;
	name: string;
	required: boolean;
	width: number;
	min: number;
	max: number;
	disabled: boolean;
	hasTicks: boolean;
	hasLabels: boolean;
	type: 'number' | 'range' | 'numberpicker';
	onChange: (value: FieldValue) => void;
	value: string;
};

const NumberInput = (props: NumberInputProps) => {
	const { label, name, required, width, min, max, disabled, hasTicks, hasLabels, onChange, value } = props;

	const rangeRef = useRef<HTMLInputElement>(null);
	const valueDisplayId = `${name}-value`;

	const onChangeHandler = (event: ChangeEvent<HTMLInputElement>) => {
		onChange(event.target.value);
	};

	const rangeValue = parseInt(value) || min;

	const classes = [
		'ctx-form-field',
		'range',
		'range--ticks',
		'input--width-' + width,
		required ? 'input--required' : '',
	].join(' ');

	return (
		<div className={classes} style={{ gridColumn: `span ${width}` }}>
			<label htmlFor={name}>{label}</label>
			<div className="range__set">
				<div className="range__control">
					<input
						id={name}
						name={name}
						required={required}
						aria-required={required}
						aria-valuemin={min}
						aria-valuemax={max}
						aria-valuenow={rangeValue}
						aria-controls={valueDisplayId}
						disabled={disabled}
						type="range"
						max={max}
						min={min}
						style={{ backgroundSize: ((rangeValue - min) * 100) / (max - min) + '% 100%' }}
						ref={rangeRef}
						onChange={onChangeHandler}
						value={value}
					/>
					{hasTicks && (
						<div className="range__ticks" aria-hidden="true">
							{[...Array(max - min + 1)].map((_, index) => (
								<div className="range__tick" key={index} />
							))}
						</div>
					)}
					{hasLabels && (
						<div className="range__labels" aria-hidden="true">
							<span className="range__label">{min}</span>
							<span className="range__label">{max}</span>
						</div>
					)}
				</div>
				<span id={valueDisplayId} className="range__value" aria-live="polite">
					{rangeValue}
				</span>
			</div>
		</div>
	);
};

NumberInput.defaultProps = {
	label: '',
	placeholder: '0',
	name: '',
	required: false,
	width: 6,
	min: 0,
	max: 100,
	hasLabels: false,
	hasTicks: false,
};

export default NumberInput;
