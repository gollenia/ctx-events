import { type VisibilityRule, VisibilityRules } from '@events/form';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type RadioAttributes = {
	width: number;
	required: boolean;
	options: string[];
	visibilityRule?: VisibilityRule | null;
};

interface InspectorProps {
	attributes: RadioAttributes;
	setAttributes: (attributes: Partial<RadioAttributes>) => void;
	clientId: string;
}

const Inspector = (props: InspectorProps) => {
	const {
		attributes: { width, required, options, visibilityRule },
		setAttributes,
		clientId,
	} = props;

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'ctx-events')}
					checked={required}
					onChange={(value) => setAttributes({ required: value })}
				/>

				<TextareaControl
					label={__('Options', 'ctx-events')}
					value={options.join('\n')}
					onChange={(value) => setAttributes({ options: value.split('\n') })}
					help={__(
						'Options for the radio control. Each line represents one option',
						'events',
					)}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<RangeControl
					label={__('Width', 'ctx-events')}
					help={__(
						'Number of columns the input field will occupy',
						'ctx-events',
					)}
					value={width}
					max={4}
					min={1}
					onChange={(value) => setAttributes({ width: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Behavior', 'ctx-events')} initialOpen={false}>
				<VisibilityRules
					clientId={clientId}
					visibilityRule={visibilityRule ?? null}
					onChange={(nextRule) =>
						setAttributes({ visibilityRule: nextRule ?? undefined })
					}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
