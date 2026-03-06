import { type VisibilityRule, VisibilityRules } from '@events/form';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type PhoneAttributes = {
	width: number;
	required: boolean;
	description: string;
	visibilityRule?: VisibilityRule | null;
};

interface InspectorProps {
	attributes: PhoneAttributes;
	setAttributes: (attributes: Partial<PhoneAttributes>) => void;
	clientId: string;
}

const Inspector = (props: InspectorProps) => {
	const {
		attributes: { width, required, description, visibilityRule },
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

				<TextControl
					label={__('Description', 'ctx-events')}
					help={__('Description for the input field', 'ctx-events')}
					value={description}
					onChange={(value) => setAttributes({ description: value })}
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
					max={6}
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
