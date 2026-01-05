/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Inspector from './inspector.js';
import { FieldHeader, useFieldProps } from '@events/form';

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
			/>
			<div className="ctx:event-field__select">
				<fieldset>
					{attributes.options.map((option, index) => {
						return (
							<div>
								<input
									type="radio"
									name={attributes.name}
									value={index}
									checked={attributes.defaultValue == index}
									onChange={() => {
										setAttributes({ defaultValue: index });
									}}
								/>
								<label>{option}</label>
							</div>
						);
					})}
				</fieldset>
			</div>
		</div>
	);
};

export default edit;
