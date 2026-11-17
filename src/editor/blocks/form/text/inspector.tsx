import { type VisibilityRule, VisibilityRules } from '@events/form';
import ContextControl from '@events/form/components/ContextControl';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
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
		attributes: {
			width,
			required,
			pattern,
			name,
			visibilityRule,
			context,
			autoComplete,
		},
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
				<SelectControl
					label={__('Browser autofill', 'ctx-events')}
					help={__(
						'Choose what information this field contains so browsers can fill it in.',
						'ctx-events',
					)}
					value={autoComplete || ''}
					onChange={(value) => setAttributes({ autoComplete: value })}
					options={[
						{ label: __('Automatic', 'ctx-events'), value: '' },
						{ label: __('Disabled', 'ctx-events'), value: 'off' },
						{ label: __('First name', 'ctx-events'), value: 'given-name' },
						{ label: __('Last name', 'ctx-events'), value: 'family-name' },
						{ label: __('Full name', 'ctx-events'), value: 'name' },
						{
							label: __('Street address', 'ctx-events'),
							value: 'address-line1',
						},
						{
							label: __('Address line 2', 'ctx-events'),
							value: 'address-line2',
						},
						{ label: __('Postal code', 'ctx-events'), value: 'postal-code' },
						{ label: __('City', 'ctx-events'), value: 'address-level2' },
						{
							label: __('State or region', 'ctx-events'),
							value: 'address-level1',
						},
						{ label: __('Organization', 'ctx-events'), value: 'organization' },
					]}
				/>
				<TextControl
					label={__('Pattern', 'ctx-events')}
					help={__(
						'Regular expression to prevent wrong or illegal input',
						'ctx-events',
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
