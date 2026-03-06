import { type VisibilityRule, VisibilityRules } from '@events/form';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type TextareaAttributes = {
	width: number;
	required: boolean;
	rows: number;
	description: string;
	visibilityRule?: VisibilityRule | null;
};

interface InspectorProps {
	attributes: TextareaAttributes;
	setAttributes: (attributes: Partial<TextareaAttributes>) => void;
	clientId: string;
}

const Inspector = (props: InspectorProps) => {
	const {
		attributes: { width, required, rows, description, visibilityRule },
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
					label={__('Pattern', 'ctx-events')}
					help={__('Help text for the input field', 'ctx-events')}
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
					max={4}
					min={1}
					onChange={(value) => setAttributes({ width: value })}
				/>
				<RangeControl
					label={__('Height', 'ctx-events')}
					help={__('Number of text rows', 'ctx-events')}
					value={rows}
					onChange={(value) => setAttributes({ rows: value ?? 1 })}
					min={1}
					max={12}
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
