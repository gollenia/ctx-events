import type { FieldRenderProps } from './types';
import Checkbox from './Fields/Checkbox';
import Combobox from './Fields/Combobox';
import Country from './Fields/Country';
import Hidden from './Fields/Hidden';
import HTMLBlock from './Fields/HtmlBlock';
import Input from './Fields/Input';
import RangeInput from './Fields/Range';
import Radio from './Fields/Radio';
import Select from './Fields/Select';
import Submit from './Fields/Submit';
import TextArea from './Fields/TextArea';

const InputField = (props: FieldRenderProps): JSX.Element | null => {
	const { type, onChange, disabled } = props;

	const base = {
		name: props.name,
		label: props.label ?? '',
		width: props.width ?? 6,
		required: props.required ?? false,
		disabled,
		formTouched: props.formTouched,
		customErrorMessage: props.customErrorMessage,
		placeholder: props.placeholder ?? '',
	};

	switch (type) {
		case 'select':
			return (
				<Select
					{...base}
					options={props.options}
					hasEmptyOption={props.hasEmptyOption}
					help={props.help}
					hint={props.hint}
					multiple={props.multiple}
					error={props.error}
					value={props.value as string}
					onChange={onChange}
				/>
			);
		case 'combobox':
			return (
				<Combobox
					{...base}
					options={Array.isArray(props.options) ? props.options : []}
					hasEmptyOption={props.hasEmptyOption}
					help={props.help ?? ''}
					hint={props.hint ?? ''}
					multiple={props.multiple ?? false}
					customError={props.customError ?? ''}
					value={props.value as string}
					onChange={onChange}
				/>
			);
		case 'radio':
		case 'options':
			return (
				<Radio
					{...base}
					options={Array.isArray(props.options) ? props.options : []}
					value={props.value as string}
					onChange={onChange}
				/>
			);
		case 'textarea':
			return (
				<TextArea
					{...base}
					rows={props.rows ?? 3}
					error={props.error}
					value={props.value as string}
					onChange={onChange}
				/>
			);
		case 'checkbox':
		case 'toggle':
			return (
				<Checkbox
					{...base}
					type={type}
					toggle={type === 'toggle'}
					defaultChecked={false}
					help={props.help}
					error={props.error}
					value={props.value as boolean}
					onChange={onChange}
				/>
			);
		case 'country':
			return (
				<Country
					{...base}
					region={props.region ?? 'world'}
					help={props.help}
					customError={props.customError ?? ''}
					error={props.error}
					value={props.value as string}
					onChange={onChange}
				/>
			);
		case 'number':
		case 'range':
		case 'numberpicker':
			return (
				<RangeInput
					{...base}
					type={type}
					min={props.min ?? 0}
					max={props.max ?? 100}
					hasTicks={props.hasTicks ?? false}
					hasLabels={props.hasLabels ?? false}
					value={props.value as string}
					onChange={onChange}
				/>
			);
		case 'html':
			return <HTMLBlock content={props.content} width={props.width} />;
		case 'hidden':
			return (
				<Hidden
					name={props.name}
					defaultValue={typeof props.defaultValue === 'string' ? props.defaultValue : undefined}
				/>
			);
		case 'submit':
			return (
				<Submit
					label={props.label ?? ''}
					width={props.width ?? 6}
					alignment={props.alignment ?? 'left'}
					placeholder={props.placeholder}
					disabled={disabled}
					type="submit"
				/>
			);
		default:
			return (
				<Input
					{...base}
					type={type as import('./Fields/Input').InputFieldTypes}
					min={props.min}
					max={props.max}
					pattern={props.pattern ?? null}
					autoComplete={props.autoComplete ?? ''}
					defaultValue={typeof props.defaultValue === 'string' ? props.defaultValue : ''}
					customError={props.customError ?? ''}
					help={props.help}
					error={props.error}
					value={props.value as string}
					onChange={onChange}
				/>
			);
	}
};

export default InputField;
