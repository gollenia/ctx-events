import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import EventSelector from './EventSelector';
import Inspector from './inspector';
import { useResolvedFeaturedEventId } from './shared';
import type {
	FeaturedEventAttributes,
	FeaturedEventContext,
} from './types';

type EditProps = {
	attributes: FeaturedEventAttributes;
	className?: string;
	context?: FeaturedEventContext;
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
	'ctx-events/featured-schedule',
	'ctx-events/featured-button',
];

export default function Edit(props: EditProps) {
	const isPatternPreview = props.isSelectionEnabled === false;
	const selectionMode = props.attributes.selectionMode ?? 'manual';
	const isCurrentContext = props.context?.postType === 'ctx-event';
	const resolvedEventId = useResolvedFeaturedEventId({
		...props.context,
		'ctx-events/eventId': props.attributes.selectedEvent,
		'ctx-events/selectionMode': props.attributes.selectionMode,
		'ctx-events/queryCategoryIds': props.attributes.queryCategoryIds,
		'ctx-events/queryTagIds': props.attributes.queryTagIds,
		'ctx-events/queryLocationId': props.attributes.queryLocationId,
		'ctx-events/queryScope': props.attributes.queryScope,
	});
	const needsExplicitSelection =
		!isPatternPreview &&
		selectionMode === 'manual' &&
		!isCurrentContext &&
		(props.attributes.selectedEvent ?? 0) <= 0;
	const needsCurrentEventContext =
		!isPatternPreview && selectionMode === 'current' && !isCurrentContext;

	useEffect(() => {
		if (selectionMode !== 'query') {
			return;
		}

		if (!resolvedEventId || resolvedEventId === (props.attributes.selectedEvent ?? 0)) {
			return;
		}

		props.setAttributes({ selectedEvent: resolvedEventId });
	}, [
		props,
		resolvedEventId,
		selectionMode,
	]);

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
			{needsCurrentEventContext ? (
				<Placeholder
					label={__('Featured Event', 'ctx-events')}
					instructions={__(
						'Current event mode only works inside an event post.',
						'ctx-events',
					)}
				/>
			) : needsExplicitSelection ? (
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
