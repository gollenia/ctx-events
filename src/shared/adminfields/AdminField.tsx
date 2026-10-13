import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	CheckboxControl,
	ComboboxControl,
	Flex,
	FlexBlock,
	__experimentalHeading as Heading,
	SelectControl,
	TextareaControl,
	TextControl,
} from '@wordpress/components';
import type { HeadingSize } from '@wordpress/components/build-types/heading/types';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import CurrencyField from './CurrencyField';
import NumberField from './NumberField';

type OptionMap = Record<string, string | number>;

type PageOption = {
	id: number;
	title: {
		rendered: string;
	};
};

type AdminFieldProps = {
	type?: string;
	label?: string;
	value?: string | number | boolean;
	onChange: (value: string | number | boolean | null) => void;
	help?: string;
	error?: string;
	required?: boolean;
	name?: string;
	level?: HeadingSize;
	options?: OptionMap;
	min?: number;
	max?: number;
	step?: number | 'any';
	disabled?: boolean;
	currency?: string;
	[key: string]: unknown;
};

const AdminField = ({
	type = 'text',
	label = '',
	value = '',
	onChange,
	help,
	error,
	required,
	name,
	...props
}: AdminFieldProps) => {
	const [pages, setPages] = useState<PageOption[]>([]);
	const [loading, setLoading] = useState<boolean>(false);
	const [passwordVisible, setPasswordVisible] = useState(false);

	useEffect(() => {
		if (type !== 'page_select') {
			setLoading(false);
			return;
		}

		setLoading(true);
		apiFetch({ path: '/wp/v2/pages?per_page=100' })
			.then((data) => {
				setPages((data as PageOption[]) ?? []);
				setLoading(false);
			})
			.catch((fetchError) => {
				console.error(fetchError);
				setPages([]);
				setLoading(false);
			});
	}, [type]);

	const classes = [required && 'required-field', error && 'error-field']
		.filter(Boolean)
		.join(' ');

	if (type === 'heading') {
		return <Heading level={props.level ?? 2}>{label}</Heading>;
	}

	if (type === 'html') {
		return (
			<div className="components-base-control">
				{label && <div className="components-base-control__label">{label}</div>}
				<code>{String(value ?? '')}</code>
				{help && <p className="components-base-control__help">{help}</p>}
			</div>
		);
	}

	if (type === 'textarea') {
		return (
			<TextareaControl
				label={label}
				value={String(value ?? '')}
				onChange={onChange}
				help={help}
				__nextHasNoMarginBottom
			/>
		);
	}

	if (type === 'select' || type === 'radio') {
		const mappedOptions = Object.entries(props.options).map(
			([optionLabel, optionValue]) => ({
				label: optionLabel,
				value: String(optionValue),
			}),
		);

		return (
			<SelectControl
				label={label}
				value={String(value ?? '')}
				onChange={onChange}
				help={help}
				options={mappedOptions}
				__nextHasNoMarginBottom
			/>
		);
	}

	if (type === 'checkbox') {
		return (
			<CheckboxControl
				label={help ?? label}
				onChange={(checked) => onChange(checked)}
				checked={Boolean(value)}
				__nextHasNoMarginBottom
			/>
		);
	}

	if (type === 'date') {
		return (
			<TextControl
				className={classes}
				label={`${label}${required ? ' *' : ''}`}
				value={String(value ?? '')}
				onChange={onChange}
				help={help}
				type="date"
				required={required}
				__nextHasNoMarginBottom
			/>
		);
	}

	if (type === 'number') {
		return (
			<NumberField
				label={label}
				value={typeof value === 'boolean' ? '' : value}
				onChange={onChange}
				help={help}
				className={classes}
				required={required}
				min={props.min}
				max={props.max}
				step={props.step}
				disabled={props.disabled}
			/>
		);
	}

	if (type === 'currency') {
		return (
			<CurrencyField
				label={label}
				value={typeof value === 'boolean' ? '' : value}
				onChange={onChange}
				help={help}
				className={classes}
				required={required}
				min={props.min}
				max={props.max}
				step={props.step}
				disabled={props.disabled}
				currency={props.currency ?? 'EUR'}
			/>
		);
	}

	if (type === 'page_select') {
		return (
			<ComboboxControl
				label={label}
				value={value == null ? '' : String(value)}
				onChange={(nextValue) => onChange(nextValue ? Number(nextValue) : null)}
				help={help}
				disabled={loading}
				options={pages.map((option) => ({
					label: option.title.rendered,
					value: String(option.id),
				}))}
				__nextHasNoMarginBottom
			/>
		);
	}

	if (type === 'password') {
		return (
			<div className="components-base-control">
				<Flex gap="8px" align="flex-end">
					<FlexBlock flex="1" as="div">
						<TextControl
							value={String(value ?? '')}
							label={`${label}${required ? ' *' : ''}`}
							className={classes}
							onChange={onChange}
							help={help}
							type={passwordVisible ? 'text' : 'password'}
							data-bwignore="true"
							data-lpignore="true"
							data-1p-ignore
							required={required}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
					</FlexBlock>
					<Button
						type="button"
						variant="secondary"
						onClick={() => setPasswordVisible((visible) => !visible)}
						aria-pressed={passwordVisible}
						__next40pxDefaultSize
					>
						{passwordVisible
							? __('Hide', 'ctx-events')
							: __('Show', 'ctx-events')}
					</Button>
				</Flex>
			</div>
		);
	}

	return (
		<TextControl
			value={String(value ?? '')}
			label={`${label}${required ? ' *' : ''}`}
			className={classes}
			onChange={onChange}
			help={help}
			type={type}
			data-bwignore="true"
			data-lpignore="true"
			data-1p-ignore
			data-protonpass-ignore="true"
			required={required}
			__nextHasNoMarginBottom
		/>
	);
};

export default AdminField;
