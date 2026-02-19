import { InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	CheckboxControl,
	Icon,
	__experimentalNumberControl as NumberControl,
	PanelBody,
	RangeControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import icons from './icons.js';
import { VisibilityRules } from '@events/form';

const Inspector = (props) => {
	const {
		attributes: {
			width,
			required,
			name,
			range,
			min,
			max,
			step,
			defaultValue,
			hasLabels,
			hasTicks,
		},
		setAttributes,
	} = props;

	const lockName = ['first_name', 'last_name'].includes(name);

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'ctx-events')}
					checked={required}
					disabled={lockName}
					onChange={(value) => setAttributes({ required: value })}
				/>

				<div className="ctx:form__panel-row">
					<NumberControl
						label={__('Minimum value', 'ctx-events')}
						value={min}
						onChange={(value) => {
							if (defaultValue < value) {
								setAttributes({ defaultValue: value });
							}
							setAttributes({ min: value });
						}}
					/>
					<NumberControl
						label={__('Maximum value', 'ctx-events')}
						value={max}
						onChange={(value) => {
							if (defaultValue > value) {
								setAttributes({ defaultValue: max });
							}
							setAttributes({ max: value });
						}}
					/>
				</div>
				<NumberControl
					label={__('Step', 'ctx-events')}
					value={step}
					max={max}
					onChange={(value) => setAttributes({ step: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<div className="styleSelector">
					<Button
						onClick={() => setAttributes({ range: false })}
						className={range ? '' : 'active'}
					>
						<Icon size="40" className="icon" icon={icons.number} />
						<span>{__('Input', 'ctx-events')}</span>
					</Button>
					<Button
						onClick={() => setAttributes({ range: true })}
						className={range ? 'active' : ''}
					>
						<Icon size="40" className="icon" icon={icons.range} />

						<span>{__('Range', 'ctx-events')}</span>
					</Button>
				</div>

				<CheckboxControl
					label={__('Show labels', 'ctx-events')}
					checked={hasLabels}
					onChange={(value) => setAttributes({ hasLabels: value })}
				/>

				<CheckboxControl
					label={__('Show ticks', 'ctx-events')}
					checked={hasTicks}
					onChange={(value) => setAttributes({ hasTicks: value })}
				/>

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
