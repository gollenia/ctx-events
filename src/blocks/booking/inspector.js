import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Inspector = ( props ) => {
	const {
		buttonColor,
		setButtonColor,
		setAttributes,
		meta,
		setMeta,
		attributes: { buttonIcon, iconRight, bookNow, customButtonColor },
	} = props;

	return (
		<>
			<InspectorControls group="styles">
				<PanelBody title={ __( 'Button Settings', 'events' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Button Icon', 'events' ) }
						value={ buttonIcon }
						onChange={ ( value ) => {
							setAttributes( { buttonIcon: value } );
						} }
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
};

export default Inspector;
