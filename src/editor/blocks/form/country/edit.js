/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Icon } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { validFieldName } from '../../../shared/fieldHelpers.js';

/**
 * Internal dependencies
 */
import icon from './icon.js';
import Inspector from './inspector.js';
import { getByRegion } from './countries.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const {
		attributes: { width, required, defaulValue, label, name, region },
		setAttributes,
	} = props;

	const locale = document.documentElement.lang

	const countryCodes = getByRegion(region)
	const regionNames = new Intl.DisplayNames([locale], { type: 'region' });

	const setName = (value) => {
		value = value.toLowerCase();
		value = value.replace(/\s/g, '-');
		setAttributes({ name: value.toLowerCase() });
	};

	const blockProps = useBlockProps({
		className: [
			'ctx:event-field',
			'ctx:event-field--' + width,
			validFieldName(name) ? '' : 'ctx:event-field--error',
		]
			.filter(Boolean)
			.join(' '),
	});

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="ctx:event-field__caption">
				<div className="ctx:event-field__info">
					<Icon icon={icon} />
					<div className="ctx:event-field__description">
						<span>
							<RichText
								tagName="span"
								className="ctx:event-field__label"
								value={label}
								placeholder={__('Label', 'events')}
								onChange={(value) => setAttributes({ label: value })}
							/>

							<span>{required ? '*' : ''}</span>
						</span>
						<span className="ctx:event-field__label">
							{__('Label for the field', 'events')}
						</span>
					</div>
				</div>

				<div className="ctx:event-field__name">
					<RichText
						tagName="p"
						className="ctx:event-field__label"
						value={name}
						placeholder={__('Slug', 'events')}
						onChange={(value) => setName(value)}
					/>
					{validFieldName(name) == false && (
						<span className="ctx:event-field__error-message">
							{__('Please type in a unique itentifier for the field', 'events')}
						</span>
					)}
					{validFieldName(name) && (
						<span className="ctx:event-field__label">
							{__('Unique identifier', 'events')}
						</span>
					)}
				</div>
			</div>
			<select
				onChange={(event) => {
					setAttributes({ placeholder: event.target.value });
				}}
			>
				{countryCodes.map((country, index) => {
					if (!country) return null;
					return (
						<option
							key={index}
							value={country}
							selected={defaulValue == country}
						>
							{regionNames.of(country)}
						</option>
					);
				})}
			</select>
		</div>
	);
};

export default edit;
