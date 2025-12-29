import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Inspector = (props) => {
	const {
		attributes: { width, required, rows, name },
		setAttributes,
	} = props;

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'events')}
					checked={required}
					onChange={(value) => setAttributes({ required: value })}
				/>

				<TextControl
					label={__('Pattern', 'events')}
					help={__('Help text for the input field', 'events')}
					value={help}
					onChange={(value) => setAttributes({ help: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'events')} initialOpen={true}>
				<RangeControl
					label={__('Width', 'events')}
					help={__('Number of columns the input field will occupy', 'events')}
					value={width}
					max={4}
					min={1}
					onChange={(value) => setAttributes({ width: value })}
				/>
				<RangeControl
					label={__('Height', 'events')}
					help={__('Number of text rows', 'events')}
					value={rows}
					onChange={(value) => setAttributes({ rows: value })}
					min={1}
					max={12}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
