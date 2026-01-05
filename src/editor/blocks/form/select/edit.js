/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, TextareaControl, TextControl } from '@wordpress/components';
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
			
			<div className="ctx:event-field__select">
				<select value={attributes.defaultValue} onChange={(event) => setAttributes({ defaultValue: event.target.value })}>
					{attributes.hasEmptyOption && (
						<option value="">{__('Make a selection', 'events')}</option>
					)}
					{attributes.options.map((option, index) => {
						return (
							<option key={index} value={index}>
								{option}
							</option>
						);
					})}
				</select>
			</div>
		</div>
	);
};

export default edit;
