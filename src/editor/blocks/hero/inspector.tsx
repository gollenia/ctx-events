import { InspectorControls } from '@wordpress/block-editor';
import {
	CheckboxControl,
	ComboboxControl,
	PanelBody,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type EventOption = {
	label: string;
	value: number;
};

type HeroAttributes = {
	selectedEvent: number;
	showExcerpt: boolean;
	showLocation: boolean;
	showButton: boolean;
	buttonText: string;
	layout: 'split' | 'cover';
};

type InspectorProps = {
	attributes: HeroAttributes;
	eventOptions: EventOption[];
	setAttributes: (attributes: Partial<HeroAttributes>) => void;
};

export default function Inspector({
	attributes,
	eventOptions,
	setAttributes,
}: InspectorProps) {
	const {
		selectedEvent,
		showExcerpt,
		showLocation,
		showButton,
		buttonText,
		layout,
	} = attributes;

	return (
		<InspectorControls>
			<PanelBody title={__('Event', 'ctx-events')} initialOpen={true}>
				<ComboboxControl
					label={__('Selected event', 'ctx-events')}
					value={selectedEvent}
					options={eventOptions}
					onChange={(value) =>
						setAttributes({ selectedEvent: Number(value) || 0 })
					}
				/>
				<SelectControl
					label={__('Layout', 'ctx-events')}
					value={layout}
					options={[
						{ label: __('Split', 'ctx-events'), value: 'split' },
						{ label: __('Cover', 'ctx-events'), value: 'cover' },
					]}
					onChange={(value) =>
						setAttributes({ layout: value as HeroAttributes['layout'] })
					}
				/>
			</PanelBody>
			<PanelBody title={__('Content', 'ctx-events')} initialOpen={true}>
				<CheckboxControl
					label={__('Show excerpt', 'ctx-events')}
					checked={showExcerpt}
					onChange={(value) => setAttributes({ showExcerpt: value })}
				/>
				<CheckboxControl
					label={__('Show location', 'ctx-events')}
					checked={showLocation}
					onChange={(value) => setAttributes({ showLocation: value })}
				/>
				<CheckboxControl
					label={__('Show button', 'ctx-events')}
					checked={showButton}
					onChange={(value) => setAttributes({ showButton: value })}
				/>
				<TextControl
					label={__('Button text', 'ctx-events')}
					value={buttonText}
					onChange={(value) => setAttributes({ buttonText: value })}
					disabled={!showButton}
					placeholder={__('View event', 'ctx-events')}
				/>
			</PanelBody>
		</InspectorControls>
	);
}
