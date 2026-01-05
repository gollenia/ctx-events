import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { VisibilityRules } from '@events/form';
const Inspector = (props) => {
	const {
		attributes: { width },
		setAttributes,
	} = props;

	return (
		<InspectorControls>
			<PanelBody title={__('Appearance', 'events')} initialOpen={true}>
				<RangeControl
					label={__('Width', 'events')}
					help={__('Number of columns the input field will occupy', 'events')}
					value={width}
					max={4}
					min={1}
					onChange={(value) => setAttributes({ width: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Behavior', 'events')} initialOpen={false}>
				<VisibilityRules
					props={props}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
