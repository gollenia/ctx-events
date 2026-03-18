import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type BookingAttributes = {
	buttonIcon: string;
	buttonTitle: string;
};

type InspectorProps = {
	attributes: BookingAttributes;
	setAttributes: (attributes: Partial<BookingAttributes>) => void;
};

const Inspector = (props: InspectorProps) => {
	const {
		setAttributes,
		attributes: { buttonIcon, buttonTitle },
	} = props;

	return (
		<InspectorControls group="styles">
			<PanelBody title={__('Button Settings', 'ctx-events')} initialOpen={true}>
				<TextControl
					label={__('Button Label', 'ctx-events')}
					value={buttonTitle}
					onChange={(value) => {
						setAttributes({ buttonTitle: value });
					}}
				/>
				<TextControl
					label={__('Button Icon', 'ctx-events')}
					value={buttonIcon}
					onChange={(value) => {
						setAttributes({ buttonIcon: value });
					}}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
