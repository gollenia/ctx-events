import { useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { formatDate } from '../../../shared/i18n/datetime.ts';
import type {
	DetailsShutdownAttributes,
	DetailBlockContext,
	DetailBlockProps,
	EventRsvpMeta,
} from '@events/details/types';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';

type ShutdownBlockProps = DetailBlockProps<DetailsShutdownAttributes> & {
	context: DetailBlockContext;
};

const edit = (props: ShutdownBlockProps) => {
	const {
		context: { postType },
	} = props;

	if (postType !== 'ctx-event') {
		return null;
	}

	const [meta] = useEntityProp('postType', postType, 'meta') as [
		EventRsvpMeta,
	];
	const blockProps = useBlockProps({ className: 'event-details-item' });

	const endFormatted = () =>
		meta._event_rsvp_end ? formatDate(meta._event_rsvp_end) : '';

	const startFormatted = () =>
		meta._event_rsvp_start ? formatDate(meta._event_rsvp_start) : '';

	const start = meta._event_rsvp_start
		? new Date(meta._event_rsvp_start)
		: null;
	const end = meta._event_rsvp_end ? new Date(meta._event_rsvp_end) : null;
	const now = new Date();

	const bookingEnded = end ? end < now : false;
	const bookingStarted = start ? start < now : false;

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="event-details__item">
				<div className="event-details__icon">
					<EventIcon name="booking_closed" />
				</div>
				<div>
					<h4 className="event-details_title">
						{bookingEnded
							? __('Booking ended', 'ctx-events')
							: bookingStarted
								? __('Booking end', 'ctx-events')
								: __('Booking start', 'ctx-events')}
					</h4>

					<span className="event-details_audience description-editable">
						{bookingEnded || bookingStarted ? endFormatted() : startFormatted()}
					</span>
				</div>
			</div>
		</div>
	);
};

export default edit;
