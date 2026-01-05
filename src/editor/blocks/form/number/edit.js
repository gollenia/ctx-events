/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Icon, RangeControl } from '@wordpress/components';
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

			{attributes.variant === 'range' ? (
				<RangeControl
					value={attributes.placeholder}
					onChange={(value) => setAttributes({ placeholder: value })}
					min={attributes.min}
					max={attributes.max}
					step={attributes.step}
				/>
			) : (
				<input
					autocomplete="off"
					value={attributes.placeholder}
					type="number"
					onChange={(event) =>
						setAttributes({ placeholder: event.target.value })
					}
				/>
			)}
		</div>
	);
};

export default edit;
