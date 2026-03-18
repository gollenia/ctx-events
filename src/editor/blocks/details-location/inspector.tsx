import { InspectorControls, URLInput } from '@wordpress/block-editor';
import { CheckboxControl, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type {
	DetailsLocationAttributes,
	SetBlockAttributes,
} from '@events/details/types';

type InspectorProps = {
	attributes: DetailsLocationAttributes;
	setAttributes: SetBlockAttributes<DetailsLocationAttributes>;
};

const Inspector = (props: InspectorProps) => {
	const { attributes, setAttributes } = props;
	const {
		showAddress,
		showCity,
		showZip,
		showCountry,
		showLink,
		showTitle,
		url,
	} = attributes;

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<CheckboxControl
					label={__('Show Title', 'ctx-events')}
					checked={showTitle}
					onChange={(value) => setAttributes({ showTitle: value })}
				/>
				<CheckboxControl
					label={__('Show Address', 'ctx-events')}
					checked={showAddress}
					onChange={(value) => setAttributes({ showAddress: value })}
				/>
				<CheckboxControl
					label={__('Show Zip', 'ctx-events')}
					checked={showZip}
					onChange={(value) => setAttributes({ showZip: value })}
				/>
				<CheckboxControl
					label={__('Show City', 'ctx-events')}
					checked={showCity}
					onChange={(value) => setAttributes({ showCity: value })}
				/>
				<CheckboxControl
					label={__('Show Country', 'ctx-events')}
					checked={showCountry}
					onChange={(value) => setAttributes({ showCountry: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Behaviour', 'ctx-events')} initialOpen={true}>
				<CheckboxControl
					label={__('Show Link', 'ctx-events')}
					checked={showLink}
					onChange={(value) => setAttributes({ showLink: value })}
				/>
				<URLInput
					label={__('Custom Link', 'ctx-events')}
					value={url}
					onChange={(value) => setAttributes({ url: value })}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
