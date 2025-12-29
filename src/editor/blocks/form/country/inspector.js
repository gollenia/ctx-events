import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const Inspector = (props) => {
	const {
		attributes: { width, required, error, region, help },
		setAttributes,
	} = props;

	const regions = [
		{ value: 'ALL', label: __('World', 'events') },
		{ value: 'DACH', label: __('DACH', 'events') },
		{ value: 'EU', label: __('Europe', 'events') },
		{ value: 'AS', label: __('Asia', 'events') },
		{ value: 'AF', label: __('Africa', 'events') },
		{ value: 'NA', label: __('North America', 'events') },
		{ value: 'SA', label: __('South America', 'events') },
		{ value: 'OC', label: __('Oceania', 'events') }
	];

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'events')}
					checked={required}
					onChange={(value) => setAttributes({ required: value })}
				/>

				<SelectControl
					label={__('Region', 'events')}
					value={region}
					options={regions}
					onChange={(value) => setAttributes({ region: value })}
				/>

				<TextControl
					label={__('Empty option', 'events')}
					help={__('Text to display when no country is selected', 'events')}
					value={help}
					onChange={(value) => setAttributes({ help: value })}
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
		</InspectorControls>
	);
};

export default Inspector;
