import { InspectorControls } from '@wordpress/block-editor';
import { useOtherFormFields } from '@events/form';
import {
	Button,
	Icon,
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
	SelectControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import icons from './icons.js';
import { VisibilityRules } from '@events/form';

const Inspector = (props) => {
	const {
		attributes: {
			width,
			required,
			variant,
			requiredMessage,
			visibilityRule
		},
		setAttributes,
	} = props;

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'ctx-events')}
					checked={required}
					onChange={(value) => setAttributes({ required: value })}
				/>

				<TextControl
					label={__('Error message', 'ctx-events')}
					help={__(
						'Text to inform the user that this checkbox must be checked',
						'events',
					)}
					value={requiredMessage}
					onChange={(value) => setAttributes({ requiredMessage: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<div>
					<label
						className="components-base-control__label"
						htmlFor="inspector-range-control-4"
					>
						{__('Style', 'ctx-events')}
					</label>
					<div className="styleSelector">
						<Button
							onClick={() => setAttributes({ variant: 'checkbox' })}
							className={variant == 'checkbox' ? 'active' : ''}
						>
							<Icon size="64" className="icon" icon={icons.checkbox} />
							<div>{__('Box', 'ctx-events')}</div>
						</Button>
						<Button
							onClick={() => setAttributes({ variant: 'toggle' })}
							className={variant == 'toggle' ? 'active' : ''}
						>
							<Icon size="64" className="icon" icon={icons.toggle} />
							<div>{__('Toggle', 'ctx-events')}</div>
						</Button>
					</div>
				</div>

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
