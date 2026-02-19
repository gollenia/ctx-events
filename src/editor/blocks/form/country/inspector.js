import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { VisibilityRules } from '@events/form';
import { getCountriesByRegion } from '@events/i18n';

const Inspector = (props) => {
	const {
		attributes: { width, required, error, region, help },
		setAttributes,
	} = props;

	const regions = [
		{ value: 'ALL', label: __('World', 'ctx-events') },
		{ value: 'DACH', label: __('DACH', 'ctx-events') },
		{ value: 'EU', label: __('Europe', 'ctx-events') },
		{ value: 'AS', label: __('Asia', 'ctx-events') },
		{ value: 'AF', label: __('Africa', 'ctx-events') },
		{ value: 'NA', label: __('North America', 'ctx-events') },
		{ value: 'SA', label: __('South America', 'ctx-events') },
		{ value: 'OC', label: __('Oceania', 'ctx-events') }
	];

	useEffect(() => {
        const codesToSave = region === 'ALL' ? [] : getCountriesByRegion(region);
        if (JSON.stringify(codesToSave) !== JSON.stringify(allowedCountries)) {
            setAttributes({ allowedCountries: codesToSave });
        }
    }, [region]);

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'ctx-events')}
					checked={required}
					onChange={(value) => setAttributes({ required: value })}
				/>

				<SelectControl
					label={__('Region', 'ctx-events')}
					value={region}
					options={regions}
					onChange={(value) => setAttributes({ region: value })}
				/>

				<TextControl
					label={__('Empty option', 'ctx-events')}
					help={__('Text to display when no country is selected', 'ctx-events')}
					value={help}
					onChange={(value) => setAttributes({ help: value })}
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
					props={props}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
