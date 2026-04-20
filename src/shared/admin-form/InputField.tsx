import {
	Button,
	CheckboxControl,
	ComboboxControl,
	RadioControl,
	RangeControl,
	SelectControl,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import type { JSX } from 'react';
import {
	type FieldRenderProps,
	getCountryOptions,
	type SelectOptions,
	sanitizeHtml,
} from '@contexis/wp-react-form';

type NormalizedOption = {
	label: string;
	value: string;
};

const browserLocale =
	typeof navigator !== 'undefined' ? navigator.language : 'en';

const normalizeOptions = (options?: SelectOptions): NormalizedOption[] => {
	if (!options) {
		return [];
	}

	if (Array.isArray(options)) {
		return options.map((option) => ({
			label: option,
			value: option,
		}));
	}

	return Object.entries(options).map(([value, label]) => ({
		label,
		value,
	}));
};

const getHelpText = (
	help?: string,
	hint?: string,
): JSX.Element | string | undefined => {
	if (help && hint) {
		return (
			<>
				<div>{hint}</div>
				<div>{help}</div>
			</>
		);
	}

	return hint ?? help;
};

const getLabel = (label?: string, required?: boolean): string =>
	required && label ? `${label} *` : (label ?? '');

const InputField = (props: FieldRenderProps): JSX.Element | null => {
	const {
		type,
		label,
		required,
		disabled,
		value,
		onChange,
		help,
		hint,
		error,
		placeholder,
		options,
		min,
		max,
		rows,
		content,
		region,
		alignment,
	} = props;

	const normalizedOptions = normalizeOptions(options);
	const helpText = getHelpText(help, hint);
	const controlLabel = getLabel(label, required);

	switch (type) {
		case 'textarea':
			return (
				<TextareaControl
					label={controlLabel}
					value={String(value ?? '')}
					onChange={onChange}
					help={error ?? helpText}
					rows={rows}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);

		case 'checkbox':
			return (
				<CheckboxControl
					label={label ?? ''}
					checked={Boolean(value)}
					onChange={onChange}
					help={error ?? helpText}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);

		case 'toggle':
			return (
				<ToggleControl
					label={label ?? ''}
					checked={Boolean(value)}
					onChange={onChange}
					help={error ?? helpText}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);

		case 'select':
			return (
				<SelectControl
					label={controlLabel}
					value={String(value ?? '')}
					onChange={onChange}
					help={error ?? helpText}
					options={normalizedOptions}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);

		case 'radio':
		case 'options':
			return (
				<RadioControl
					label={controlLabel}
					selected={String(value ?? '')}
					options={normalizedOptions}
					onChange={onChange}
					help={error ?? helpText}
					disabled={disabled}
				/>
			);

		case 'combobox':
			return (
				<ComboboxControl
					label={controlLabel}
					value={String(value ?? '')}
					options={normalizedOptions}
					onChange={(nextValue) => onChange(nextValue ?? '')}
					help={error ?? helpText}
					placeholder={placeholder}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);

		case 'country':
			return (
				<ComboboxControl
					label={controlLabel}
					value={String(value ?? '')}
					options={getCountryOptions(region ?? 'world', browserLocale)}
					onChange={(nextValue) => onChange(nextValue ?? '')}
					help={error ?? helpText}
					placeholder={placeholder}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);

		case 'range':
		case 'numberpicker':
			return (
				<RangeControl
					label={controlLabel}
					value={typeof value === 'number' ? value : Number(value || 0)}
					onChange={(nextValue) => onChange(nextValue ?? 0)}
					help={error ?? helpText}
					min={min}
					max={max}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);

		case 'html':
			return (
				<div
					className="ctx-admin-form-html"
					dangerouslySetInnerHTML={{ __html: sanitizeHtml(content ?? '') }}
				/>
			);

		case 'hidden':
			return (
				<input
					type="hidden"
					name={props.name}
					value={String(value ?? props.defaultValue ?? '')}
				/>
			);

		case 'submit':
			return (
				<div
					className={`ctx-admin-form-submit ctx-admin-form-submit--${alignment ?? 'left'}`}
				>
					<Button variant="primary" disabled={disabled}>
						{label ?? ''}
					</Button>
				</div>
			);

		default:
			return (
				<TextControl
					label={controlLabel}
					value={String(value ?? '')}
					onChange={onChange}
					help={error ?? helpText}
					type={type}
					min={min}
					max={max}
					placeholder={placeholder}
					disabled={disabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					autoComplete="one-time-code"
					data-bwignore="true"
					data-lpignore="true"
					data-1p-ignore
					data-protonpass-ignore="true"
				/>
			);
	}
};

export default InputField;
