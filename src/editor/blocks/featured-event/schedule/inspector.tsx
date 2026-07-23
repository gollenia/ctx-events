import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type Props = {
	attributes: {
		displayMode?: string;
	};
	setAttributes: (attributes: { displayMode: string }) => void;
};

export default function Inspector({ attributes, setAttributes }: Props) {
	return (
		<InspectorControls>
			<PanelBody title={__('Display', 'ctx-events')} initialOpen>
				<SelectControl
					label={__('Schedule format', 'ctx-events')}
					value={attributes.displayMode ?? 'date-time'}
					options={[
						{ label: __('Date and time', 'ctx-events'), value: 'date-time' },
						{ label: __('Date only', 'ctx-events'), value: 'date' },
						{ label: __('Time only', 'ctx-events'), value: 'time' },
						{ label: __('Countdown', 'ctx-events'), value: 'countdown' },
					]}
					onChange={(value) => setAttributes({ displayMode: value })}
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			</PanelBody>
		</InspectorControls>
	);
}
