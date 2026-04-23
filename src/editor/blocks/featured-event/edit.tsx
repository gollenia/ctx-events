import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import EventSelector from './EventSelector';
import Inspector from './inspector';

type FeaturedEventAttributes = {
	selectedEvent: number;
};

type EditProps = {
	attributes: FeaturedEventAttributes;
	className?: string;
	context?: {
		postId?: number;
		postType?: string;
	};
	isSelectionEnabled?: boolean;
	setAttributes: (attributes: Partial<FeaturedEventAttributes>) => void;
};

const ALLOWED_BLOCKS = [
	'core/columns',
	'core/column',
	'core/group',
	'core/heading',
	'core/paragraph',
	'core/buttons',
	'core/button',
	'ctx-events/featured-image',
	'ctx-events/featured-title',
	'ctx-events/featured-schedule',
	'ctx-events/featured-location',
	'ctx-events/featured-excerpt',
	'ctx-events/featured-button',
];

export default function Edit(props: EditProps) {
	const isPatternPreview = props.isSelectionEnabled === false;
	const needsExplicitSelection =
		!isPatternPreview &&
		props.context?.postType !== 'ctx-event' &&
		(props.attributes.selectedEvent ?? 0) <= 0;

	const blockProps = useBlockProps({
		className: 'ctx-featured-event',
	});
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'ctx-featured-event__inner' },
		{
			allowedBlocks: ALLOWED_BLOCKS,
			templateLock: false,
		},
	);

	return (
		<div {...blockProps}>
			<Inspector {...props} />
			{needsExplicitSelection ? (
				<Placeholder
					label={__('Featured Event', 'ctx-events')}
					instructions={__(
						'Choose an event to populate this featured layout.',
						'ctx-events',
					)}
				>
					<EventSelector
						attributes={props.attributes}
						context={props.context}
						label={__('Event', 'ctx-events')}
						onChange={props.setAttributes}
					/>
				</Placeholder>
			) : (
				<div {...innerBlocksProps} />
			)}
		</div>
	);
}
