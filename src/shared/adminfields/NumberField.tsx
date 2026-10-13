import {
	__experimentalInputControlSuffixWrapper as InputControlSuffixWrapper,
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import type { ReactNode } from '@wordpress/element';

export type NumberFieldProps = {
	label?: string;
	value?: string | number;
	onChange: (value: string | number) => void;
	help?: string;
	className?: string;
	required?: boolean;
	min?: number;
	max?: number;
	step?: number | 'any';
	disabled?: boolean;
	suffix?: ReactNode;
	onBlur?: () => void;
};

const NumberField = ({
	label = '',
	value = '',
	onChange,
	help,
	className,
	required,
	min,
	max,
	step,
	disabled,
	suffix,
	onBlur,
}: NumberFieldProps) => (
	<NumberControl
		label={`${label}${required ? ' *' : ''}`}
		value={value}
		onChange={onChange}
		help={help}
		className={className}
		required={required}
		min={min}
		max={max}
		step={step}
		disabled={disabled}
		onBlur={onBlur}
		suffix={
			suffix ? (
				<InputControlSuffixWrapper>{suffix}</InputControlSuffixWrapper>
			) : undefined
		}
		__nextHasNoMarginBottom
		__next40pxDefaultSize
	/>
);

export default NumberField;
