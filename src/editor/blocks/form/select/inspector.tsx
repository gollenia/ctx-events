import { type VisibilityRule, VisibilityRules } from '@events/form';
import { InspectorControls } from '@wordpress/block-editor';
import {
	CheckboxControl,
	PanelBody,
	RangeControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type SelectAttributes = {
	width: number;
	required: boolean;
	options: string[];
	hasEmptyOption: boolean;
	visibilityRule?: VisibilityRule | null;
};

interface InspectorProps {
	attributes: SelectAttributes;
	setAttributes: (attributes: Partial<SelectAttributes>) => void;
	clientId: string;
}

const Inspector = (props: InspectorProps) => {
	const {
		attributes: { width, required, options, hasEmptyOption, visibilityRule },
		setAttributes,
		clientId,
	} = props;

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<ToggleControl
					label={__('Required', 'ctx-events')}
					checked={required}
					onChange={(value) =>
						setAttributes({ required: value, hasEmptyOption: value })
					}
				/>
				<CheckboxControl
					label={__('Empty option', 'ctx-events')}
					help={__(
						'An empty option ist shown and selected as default',
						'events',
					)}
					checked={hasEmptyOption}
					disabled={required}
					onChange={(value) => setAttributes({ hasEmptyOption: value })}
				/>

				<TextareaControl
					label={__('Options', 'ctx-events')}
					value={options.join('\n')}
					onChange={(value) => setAttributes({ options: value.split('\n') })}
					help={__(
						'Options for the select control. Each line represents one option',
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
