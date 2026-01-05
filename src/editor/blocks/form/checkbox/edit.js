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
import { useFieldProps, FieldHeader } from '@events/form';
import icons from './icons.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const {
		attributes,
		setAttributes,
	} = props;

	const blockProps = useFieldProps(attributes);

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<FieldHeader
				attributes={attributes}
				setAttributes={setAttributes}
				clientId={props.clientId}
				icon={attributes.variant == 'checkbox' ? icons.checkbox : icons.toggle}
			/>

			<div className="label">
				{attributes.variant == 'checkbox' && (
					<CheckboxControl
						checked={attributes.defaultValue}
						onChange={(value) => setAttributes({ defaultValue: value })}
					/>
				)}

				{attributes.variant == 'toggle' && (
					<ToggleControl
						checked={attributes.defaultValue}
						onChange={(value) => setAttributes({ defaultValue: value })}
					/>
				)}
				<RichText
					tagName="p"
					className="ctx:event-details__label"
					value={attributes.description}
					required
					placeholder={__('What should your visitor say "yes" to?', 'events')}
					onChange={(value) => setAttributes({ description: value })}
				/>
			</div>
		</div>
	);
};

export default edit;
