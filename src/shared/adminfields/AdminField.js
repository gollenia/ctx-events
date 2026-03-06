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

const AdminField = ({
	type,
	label,
	value,
	onChange,
	help,
	error,
	required,
	name,
	...props
}) => {
	const [pages, setPages] = useState([]);
	const [loading, setLoading] = useState(false);

	useEffect(() => {
		if (type !== 'page_select') {
			setLoading(false);
			return;
		}

		setLoading(true);
		apiFetch({ path: '/wp/v2/pages?per_page=100' })
			.then((data) => {
				setPages(data);
				setLoading(false);
			})
			.catch((err) => {
				console.error(err);
				setPages([]);
				setLoading(false);
			});
	}, [type]);

	const classes = clsx({
		'required-field': required,
		'error-field': error,
	});

	if (type === 'heading') {
		return <Heading level={props.level ?? 2}>{label}</Heading>;
	}

	if (type === 'textarea') {
		return (
			<TextareaControl
				label={label}
				value={value}
				onChange={onChange}
				help={help}
				error={error}
				required={required}
				{...props}
			/>
		);
	}

	if (type === 'select' || type === 'radio') {
		const mappedOptions = Object.entries(props.options || {}).map(
			([label, value]) => {
				return { label, value };
			},
		);

		return (
			<SelectControl
				label={label}
				value={value}
				onChange={onChange}
				onFocus={onChange}
				help={help}
				error={error}
				required={required}
				options={mappedOptions}
				defaultValue={mappedOptions[0].value}
			/>
		);
	}

	if (type === 'checkbox') {
		return (
			<CheckboxControl
				label={help ?? label}
				value={value}
				onChange={onChange}
				required={required}
				checked={value}
				type="checkbox"
			/>
		);
	}

	if (type === 'date') {
		return (
			<TextControl
				className={classes}
				label={label + (required ? ' *' : '')}
				value={value}
				onChange={onChange}
				help={help}
				error={error}
				type="date"
				required={required}
				{...props}
			/>
		);
	}

	if (type === 'page_select') {
		return (
			<ComboboxControl
				label={label}
				value={value}
				onChange={onChange}
				help={help}
				error={error}
				required={required}
				disabled={loading}
				options={pages.map((option) => ({
					label: option.title.rendered,
					value: option.id,
				}))}
			/>
		);
	}

	return (
		<TextControl
			value={value}
			label={label + (required ? ' *' : '')}
			className={classes}
			onChange={onChange}
			help={help}
			error={error}
			type={type}
			required={required}
			{...props}
		/>
	);
};

export default AdminField;
