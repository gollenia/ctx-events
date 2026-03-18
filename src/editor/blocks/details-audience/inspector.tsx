import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Inspector = () => {
	return (
		<InspectorControls>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<PanelRow />
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
