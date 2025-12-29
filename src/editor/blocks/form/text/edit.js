/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Icon } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { select, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Inspector from './inspector.js';
import lock from './lockIcon.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const {
		attributes: {
			width,
			required,
			pattern,
			placeholder,
			label,
			name,
		},
		setAttributes,
	} = props;

	const lockFieldId = ['first_name', 'last_name'].includes(name);
	const validName = () => {
		const validPattern = /([a-zA-Z0-9_]){3,40}/;
		return validPattern.test(name);
	};

	const setName = (value) => {
		value = value.toLowerCase();
		value = value.replace(/\s/g, '-');
		setAttributes({ name: value.toLowerCase() });
	};

	const blockProps = useBlockProps({
		className: [
			'ctx:event-field',
			'ctx:event-field--' + width,
			validName() == false ? 'ctx:event-field--error' : '',
		]
			.filter(Boolean)
			.join(' '),
	});

	return (
		<div {...blockProps}>
			<Inspector {...props} />
			<div className="ctx:event-field__caption">
				<div>
					<RichText
						tagName="span"
						className="ctx:event-details__label"
						value={label}
						placeholder={__('Label', 'events')}
						onChange={(value) => setAttributes({ label: value })}
					/>

					<span>{required ? '*' : ''}</span>
					<br />
					<span className="ctx:event-field__label">
						{__('Label for the field', 'events')}
					</span>
				</div>

				<div className="ctx:event-field__name">
					{!lockFieldId && (
						<RichText
							tagName="p"
							className="ctx:event-details__label"
							value={name}
							placeholder={__('Slug', 'events')}
							onChange={(value) => setName(value)}
						/>
					)}
					{lockFieldId && (
						<span className="ctx:event-details__label--lock">
							{name} <Icon icon={lock} size={14} />
						</span>
					)}
					{validName() == false && (
						<span className="ctx:event-field__error-message">
							{__('Please type in a unique itentifier for the field', 'events')}
						</span>
					)}
					{validName() && (
						<span className="ctx:event-field__label">
							{__('Unique identifier', 'events')}
						</span>
					)}
				</div>
			</div>

			<input
				autocomplete="off"
				value={placeholder}
				type="text"
				onChange={(event) => setAttributes({ placeholder: event.target.value })}
			/>
		</div>
	);
};

export default edit;
