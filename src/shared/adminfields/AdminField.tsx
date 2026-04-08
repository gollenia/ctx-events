import apiFetch from '@wordpress/api-fetch';
import {
	CheckboxControl,
	ComboboxControl,
	__experimentalHeading as Heading,
	SelectControl,
	TextareaControl,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import clsx from 'clsx';

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
	level?: number;
	options?: OptionMap;
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
	const [loading, setLoading] = useState(false);

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

	const classes = clsx({
		'required-field': required,
		'error-field': error,
	});

	if (type === 'heading') {
		return (
			<Heading level={(props.level as number | undefined) ?? 2}>
				{label}
			</Heading>
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
		const mappedOptions = Object.entries(
			props.options as OptionMap | undefined,
		).map(([optionLabel, optionValue]) => ({
			label: optionLabel,
			value: String(optionValue),
		}));

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
