import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import EventSelector from './EventSelector';

type FeaturedEventAttributes = {
	selectedEvent: number;
};

type EditProps = {
	attributes: FeaturedEventAttributes;
	context?: {
		postId?: number;
		postType?: string;
	};
	setAttributes: (attributes: Partial<FeaturedEventAttributes>) => void;
};

export default function Inspector({
	attributes,
	context,
	setAttributes,
}: EditProps) {
	return (
		<InspectorControls>
			<PanelBody title={__('Event', 'ctx-events')} initialOpen>
				<EventSelector
					attributes={attributes}
					context={context}
					onChange={setAttributes}
				/>
			</PanelBody>
		</InspectorControls>
	);
}
