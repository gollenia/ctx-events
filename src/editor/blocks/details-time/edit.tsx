import type {
	DetailBlockProps,
	DetailsTimeAttributes,
	EventDateMeta,
} from '@events/details/types';
import { formatDailyTimeRange, formatTimeRange } from '@events/i18n';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';

const edit = (props: DetailBlockProps<DetailsTimeAttributes>) => {
	const postType = (
		select('core/editor') as { getCurrentPostType: () => string }
	).getCurrentPostType();

	if (postType !== 'ctx-event') {
		return null;
	}

	const {
		attributes: { description },
		setAttributes,
	} = props;
	const [meta] = useEntityProp('postType', postType, 'meta') as [EventDateMeta];
	const blockProps = useBlockProps();

	const timeFormatted = () => {
		if (!meta?._event_start || !meta?._event_end) {
			return '';
		}

		return Boolean(Number(meta._event_daily ?? 0))
			? formatDailyTimeRange(meta._event_start, meta._event_end)
			: formatTimeRange(meta._event_start, meta._event_end);
	};

	return (
		<div {...blockProps}>
			<Inspector />

			<div className="event-details-item">
				<div className="event-details-image">
					<EventIcon name="time" />
				</div>
				<div className="event-details-text">
					<RichText
						tagName="h4"
						className="event-details-title description-editable"
						placeholder={__('Time', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<span className="event-details-data description-editable">
						{timeFormatted()}
					</span>
				</div>
			</div>
		</div>
	);
};

export default edit;
