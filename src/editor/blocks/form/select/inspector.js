import { InspectorControls } from '@wordpress/block-editor';
import {
	CheckboxControl,
	PanelBody,
	RangeControl,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { VisibilityRules } from '@events/form';

const Inspector = (props) => {
	const {
		attributes: { width, required, options, hasEmptyOption },
		setAttributes,
	} = props;

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'events')}
					checked={required}
					onChange={(value) =>
						setAttributes({ required: value, hasEmptyOption: value })
					}
				/>
				<CheckboxControl
					label={__('Empty option', 'events')}
					help={__(
						'An empty option ist shown and selected as default',
						'events',
					)}
					checked={hasEmptyOption}
					disabled={required}
					onChange={(value) => setAttributes({ hasEmptyOption: value })}
				/>

				<TextareaControl
					label={__('Options', 'events')}
					value={options.join('\n')}
					onChange={(value) => setAttributes({ options: value.split('\n') })}
					help={__(
						'Options for the select control. Each line represents one option',
						'events',
					)}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'events')} initialOpen={true}>
				<RangeControl
					label={__('Width', 'events')}
					help={__('Number of columns the input field will occupy', 'events')}
					value={width}
					max={6}
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
