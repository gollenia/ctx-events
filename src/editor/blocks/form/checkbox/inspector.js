import { InspectorControls } from '@wordpress/block-editor';
import { getNeighbourBlocks } from '../../../shared/blockHelpers.js';
import {
	Button,
	Icon,
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import icons from './icons.js';

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

	const neighbourBlocks = getNeighbourBlocks(props.clientId);

	const setVisibilityRule = (field) => {
		if (field === '') {
			setAttributes({ visibilityRule: null });
			return;
		}
		setAttributes({
			visibilityRule: {
				field,
				operator: 'equals',
				value: 'true'
			}
		});
	}

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'events')}
					checked={required}
					onChange={(value) => setAttributes({ required: value })}
				/>

				<TextControl
					label={__('Error message', 'events')}
					help={__(
						'Text to inform the user that this checkbox must be checked',
						'events',
					)}
					value={requiredMessage}
					onChange={(value) => setAttributes({ requiredMessage: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'events')} initialOpen={true}>
				<div>
					<label
						className="components-base-control__label"
						htmlFor="inspector-range-control-4"
					>
						{__('Style', 'events')}
					</label>
					<div className="styleSelector">
						<Button
							onClick={() => setAttributes({ style: 'checkbox' })}
							className={style == 'checkbox' ? 'active' : ''}
						>
							<Icon size="64" className="icon" icon={icons.checkbox} />
							<div>{__('Box', 'events')}</div>
						</Button>
						<Button
							onClick={() => setAttributes({ style: 'toggle' })}
							className={style == 'toggle' ? 'active' : ''}
						>
							<Icon size="64" className="icon" icon={icons.toggle} />
							<div>{__('Toggle', 'events')}</div>
						</Button>
					</div>
				</div>

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
				<ToggleControl
					label={__('Visibility rule', 'events')}
					onChange={(value) => setAttributes({ visibilityRule: value })}
				/>
				<SelectControl
					label={__('Field', 'events')}
					value={visibilityRule.field}
					options={neighbourBlocks}
					onChange={(value) => setAttributes({ visibilityRule: { ...visibilityRule, field: value } })}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
