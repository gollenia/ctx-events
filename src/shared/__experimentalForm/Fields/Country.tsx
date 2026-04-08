import { useMemo } from '@wordpress/element';
import { getCountryOptions } from '../countries';
import type { CountryRegion, FieldValue } from '../types';
import Combobox from './Combobox';

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
	const {
		onChange,
		disabled,
		required,
		name,
		label,
		width,
		region,
		help,
		customErrorMessage,
		error,
		value,
		placeholder,
		formTouched,
	} = props;

	const countries = useMemo(
		() => getCountryOptions(region, browserLocale),
		[region],
	);

	const labelToValueMap = useMemo(() => {
		return new Map(countries.map((country) => [country.label, country.value]));
	}, [countries]);

	const valueToLabelMap = useMemo(() => {
		return new Map(countries.map((country) => [country.value, country.label]));
	}, [countries]);

	const selectedLabel = value ? (valueToLabelMap.get(value) ?? '') : '';

	const handleChange = (selectedLabel: FieldValue) => {
		if (typeof selectedLabel !== 'string') {
			onChange('');
			return;
		}

		const selectedValue = labelToValueMap.get(selectedLabel) ?? '';
		onChange(selectedValue);
	};

	return (
		<Combobox
			label={label}
			name={name}
			required={required}
			width={width}
			options={countries.map((country) => country.label)}
			help={help ?? 'Make a selection'}
			disabled={disabled}
			formTouched={formTouched}
			customErrorMessage={customErrorMessage}
			error={error}
			placeholder={placeholder}
			value={selectedLabel}
			onChange={handleChange}
			allowClear={!required}
			clearLabel={help ?? 'Make a selection'}
			noResultsLabel="No matching country"
		/>
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
