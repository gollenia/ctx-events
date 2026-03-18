import { InspectorControls } from '@wordpress/block-editor';
import {
	CheckboxControl,
	ComboboxControl,
	PanelBody,
	RangeControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type MonthlyPdfExportAttributes = {
	title: string;
	buttonText: string;
	exportMode: 'week' | 'month' | 'year';
	periodsAhead: number;
	showEmptyDays: boolean;
	category: number;
};

type CategoryOption = {
	label: string;
	value: number;
};

type InspectorProps = {
	attributes: MonthlyPdfExportAttributes;
	categoryOptions: CategoryOption[];
	setAttributes: (attributes: Partial<MonthlyPdfExportAttributes>) => void;
};

export default function Inspector({
	attributes,
	categoryOptions,
	setAttributes,
}: InspectorProps) {
	return (
		<InspectorControls>
			<PanelBody title={__('Content', 'ctx-events')} initialOpen={true}>
				<TextControl
					label={__('Title', 'ctx-events')}
					value={attributes.title}
					onChange={(value) => setAttributes({ title: value })}
				/>
				<TextControl
					label={__('Button text', 'ctx-events')}
					value={attributes.buttonText}
					onChange={(value) => setAttributes({ buttonText: value })}
				/>
			</PanelBody>
			<PanelBody title={__('Export', 'ctx-events')} initialOpen={true}>
				<SelectControl
					label={__('Program type', 'ctx-events')}
					value={attributes.exportMode}
					options={[
						{ label: __('Week', 'ctx-events'), value: 'week' },
						{ label: __('Month', 'ctx-events'), value: 'month' },
						{ label: __('Year', 'ctx-events'), value: 'year' },
					]}
					onChange={(value) =>
						setAttributes({
							exportMode: value as MonthlyPdfExportAttributes['exportMode'],
						})
					}
				/>
				<RangeControl
					label={__('Selectable periods', 'ctx-events')}
					min={1}
					max={24}
					value={attributes.periodsAhead}
					onChange={(value) =>
						setAttributes({ periodsAhead: Number(value) || 12 })
					}
				/>
				<CheckboxControl
					label={__('Show days without events', 'ctx-events')}
					checked={attributes.showEmptyDays}
					onChange={(value) => setAttributes({ showEmptyDays: value })}
				/>
				<ComboboxControl
					label={__('Category filter', 'ctx-events')}
					value={attributes.category}
					options={categoryOptions}
					onChange={(value) =>
						setAttributes({ category: Number(value) || 0 })
					}
				/>
			</PanelBody>
		</InspectorControls>
	);
}
