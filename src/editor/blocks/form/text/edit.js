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

			<input
				autocomplete="off"
				value={attributes.placeholder}
				type="text"
				onChange={(event) => setAttributes({ placeholder: event.target.value })}
			/>
		</div>
	);
};

export default edit;
