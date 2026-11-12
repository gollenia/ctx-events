import { VisibilityRules } from '@events/form';
import ContextControl from '@events/form/components/ContextControl';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { DateAttributes } from './edit';

interface InspectorProps {
	attributes: DateAttributes;
	clientId: string;
	setAttributes: (attributes: Partial<DateAttributes>) => void;
}

const Inspector = (props: InspectorProps) => {
	const {
		attributes: {
			width,
			required,
			min,
			max,
			useEventAgeLimits,
			description,
			visibilityRule,
			context,
		},
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
				<ToggleControl
					label={__('Use age limits from the event', 'ctx-events')}
					help={__(
						'Uses the event’s minimum and maximum age to validate this birth date.',
						'ctx-events',
					)}
					checked={useEventAgeLimits}
					onChange={(value) => setAttributes({ useEventAgeLimits: value })}
				/>

				<TextControl
					label={__('Description', 'ctx-events')}
					help={__('Help text for the date field', 'ctx-events')}
					value={description}
					onChange={(value) => setAttributes({ description: value })}
				/>
				<TextControl
					label={__('Lowest Date', 'ctx-events')}
					help={__('e.g. maximal age for an attendee', 'ctx-events')}
					value={min}
					onChange={(value) => setAttributes({ min: value })}
					type="date"
					disabled={useEventAgeLimits}
				/>
				<TextControl
					label={__('Highest Date', 'ctx-events')}
					help={__('e.g. minimal age for an attendee', 'ctx-events')}
					value={max}
					onChange={(value) => setAttributes({ max: value })}
					type="date"
					disabled={useEventAgeLimits}
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
					onChange={(visibilityRule) =>
						setAttributes({ visibilityRule: visibilityRule ?? undefined })
					}
				/>

				<ContextControl
					value={context ?? ''}
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
