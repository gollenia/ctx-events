import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { VisibilityRules } from '@events/form';

const Inspector = (props) => {
	const {
		attributes: { width, required, name, description },
		setAttributes,
	} = props;

	const lockName = 'user_email' === name;

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'ctx-events')}
					checked={required}
					disabled={lockName}
					onChange={(value) => setAttributes({ required: value })}
				/>
				<TextControl
					label={__('Description', 'ctx-events')}
					value={description}
					onChange={(value) => setAttributes({ description: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<RangeControl
					label={__('Width', 'ctx-events')}
					help={__('Number of columns the input field will occupy', 'ctx-events')}
					value={width}
					max={6}
					min={1}
					onChange={(value) => setAttributes({ width: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Behavior', 'ctx-events')} initialOpen={false}>
				<VisibilityRules
					{...props}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
