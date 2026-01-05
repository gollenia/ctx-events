/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Inspector from './inspector.js';
import { useFieldProps, FieldHeader } from '@events/form';

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

			<input
				autocomplete="off"
				value={attributes.placeholder}
				type="date"
				onChange={(event) => setAttributes({ placeholder: event.target.value })}
			/>
		</div>
	);
};

export default edit;
