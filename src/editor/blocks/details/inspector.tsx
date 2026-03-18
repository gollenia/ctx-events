import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type DetailsAttributes = {
	dividers: boolean;
};

type InspectorProps = {
	attributes: DetailsAttributes;
	setAttributes: (attributes: Partial<DetailsAttributes>) => void;
};

export default function Inspector({ attributes, setAttributes }: InspectorProps) {
	return (
		<InspectorControls>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<PanelRow>
					<ToggleControl
						label={__('Lines as separators', 'ctx-events')}
						checked={attributes.dividers}
						onChange={(value) => setAttributes({ dividers: value })}
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
	);
}
