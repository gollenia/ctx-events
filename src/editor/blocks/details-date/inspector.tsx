import { InspectorControls } from '@wordpress/block-editor';
import { CheckboxControl, PanelBody, PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type {
	DetailsDateAttributes,
	EventDateMeta,
	SetBlockAttributes,
} from '@events/details/types';

type InspectorProps = {
	attributes: DetailsDateAttributes;
	meta: EventDateMeta;
	setMeta: (value: EventDateMeta) => void;
	setAttributes: SetBlockAttributes<DetailsDateAttributes>;
};

const Inspector = (props: InspectorProps) => {
	const {
		attributes: { iCalLink },
		setAttributes,
	} = props;

	return (
		<InspectorControls>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<PanelRow>
					<CheckboxControl
						label={__('Show iCal link', 'ctx-events')}
						checked={iCalLink}
						onChange={(value) => setAttributes({ iCalLink: value })}
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
