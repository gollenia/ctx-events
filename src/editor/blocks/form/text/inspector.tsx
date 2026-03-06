import { type VisibilityRule, VisibilityRules } from '@events/form';
import ContextControl from '@events/form/components/ContextControl';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import type { TextAttributes } from './edit';

interface InspectorProps {
	attributes: TextAttributes;
	setAttributes: (attributes: Partial<TextAttributes>) => void;
	clientId: string;
}

const Inspector = (props: InspectorProps) => {
	const {
		attributes: { width, required, pattern, name, visibilityRule, context },
		setAttributes,
		clientId,
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
				<TextControl
					label={__('Pattern', 'ctx-events')}
					help={__(
						'Regular expression to prevent wrong or illegal input',
						'events',
					)}
					value={pattern}
					onChange={(value) => setAttributes({ pattern: value })}
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
			<PanelBody title={__('Dependencies', 'ctx-events')} initialOpen={false}>
				<VisibilityRules
					clientId={clientId}
					visibilityRule={visibilityRule ?? null}
					onChange={(nextRule) =>
						setAttributes({ visibilityRule: nextRule ?? undefined })
					}
				/>

				<ContextControl
					value={context}
					clientId={clientId}
					onChange={(value: string) => {
						console.log('Context changed to', value);
						setAttributes({ context: value });
					}}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
