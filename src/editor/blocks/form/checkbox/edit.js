/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { CheckboxControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Inspector from './inspector.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const {
		attributes: { width, required, label, name, description, style, defaultValue },
		setAttributes,
	} = props;

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
			validName() == false || description === '' ? 'ctx:event-field--error' : '',
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
						{__('Only for internal usage - place field text below', 'events')}
					</span>
				</div>

				<div className="ctx:event-field__name">
					<RichText
						tagName="p"
						className="ctx:event-details__label"
						value={name}
						placeholder={__('Slug', 'events')}
						onChange={(value) => setName(value)}
					/>
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

			<div className="label">
				{style == 'checkbox' && (
					<CheckboxControl
						checked={defaultValue}
						onChange={(value) => setAttributes({ defaultValue: value })}
					/>
				)}

				{style == 'toggle' && (
					<ToggleControl
						checked={defaultValue}
						onChange={(value) => setAttributes({ defaultValue: value })}
					/>
				)}
				<RichText
					tagName="p"
					className="ctx:event-details__label"
					value={description}
					required
					placeholder={__('What should your visitor say "yes" to?', 'events')}
					onChange={(value) => setAttributes({ description: value })}
				/>
			</div>
		</div>
	);
};

export default edit;
