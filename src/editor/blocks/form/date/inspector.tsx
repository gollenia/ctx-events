import { VisibilityRules } from '@events/form';
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
		attributes: { width, required, min, max, description, visibilityRule },
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
				/>
				<TextControl
					label={__('Highest Date', 'ctx-events')}
					help={__('e.g. minimal age for an attendee', 'ctx-events')}
					value={max}
					onChange={(value) => setAttributes({ max: value })}
					type="date"
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
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
