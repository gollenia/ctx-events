import { VisibilityRules } from '@events/form';
import { InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	Icon,
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { CheckBoxAttributes } from './edit';
import icons from './icons';

interface InspectorProps {
	attributes: CheckBoxAttributes;
	clientId: string;
	setAttributes: (attributes: Partial<CheckBoxAttributes>) => void;
}

const Inspector = (props: InspectorProps) => {
	const {
		attributes: { width, required, variant, requiredMessage, visibilityRule },
		clientId,
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
							className={variant === 'checkbox' ? 'active' : ''}
						>
							<Icon size={64} className="icon" icon={icons.checkbox} />
							<div>{__('Box', 'ctx-events')}</div>
						</Button>
						<Button
							onClick={() => setAttributes({ variant: 'toggle' })}
							className={variant === 'toggle' ? 'active' : ''}
						>
							<Icon size={64} className="icon" icon={icons.toggle} />
							<div>{__('Toggle', 'ctx-events')}</div>
						</Button>
					</div>
				</div>

				<RangeControl
					label={__('Width', 'ctx-events')}
					help={__(
						'Number of columns the input field will occupy',
						'ctx-events',
					)}
					value={width}
					max={6}
					min={1}
					onChange={(value) => setAttributes({ width: value })}
				/>
			</PanelBody>

			<PanelBody title={__('Behavior', 'ctx-events')} initialOpen={false}>
				<VisibilityRules
					clientId={clientId}
					visibilityRule={visibilityRule ?? null}
					onChange={(visibilityRule) =>
						setAttributes({ visibilityRule: visibilityRule ?? undefined })
					}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
