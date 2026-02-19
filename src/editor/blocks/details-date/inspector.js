import { InspectorControls } from '@wordpress/block-editor';
import { CheckboxControl, PanelBody, PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Inspector = (props) => {
	const { attributes, setAttributes } = props;
	const { iCalLink } = attributes;
	return (
		<InspectorControls>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<PanelRow>
					<CheckboxControl
						label={__('Show iCal link', 'ctx-events')}
						checked={iCalLink}
						onChange={(value) => setAttributes({ iCalLink: value })}
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
