/**
 * Wordpress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { formatDate } from '../../../shared/i18n/datetime.js';
/**
 * Internal dependencies
 */

import Inspector from './inspector.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const {
		attributes: { roundImage, format, description },
		setAttributes,
	} = props;

	if (props.context.postType !== 'ctx-event') return null;

	const [meta, setMeta] = useEntityProp('postType', props.context.postType, 'meta');

	const blockProps = useBlockProps({ className: 'event-details-item' });

	const endFormatted = () => {
		return meta['_event_rsvp_end'] ? formatDate(meta['_event_rsvp_end']) : '';
	};

	console.log(meta);

	const start = new Date(meta['_event_rsvp_start']);
	const end = new Date(meta['_event_rsvp_end']);
	const now = new Date();

	const bookingEnded = end < now;
	const bookingStarted = start < now;

	const startFormatted = () => {
		return meta['_event_rsvp_start']
			? formatDate(meta['_event_rsvp_start'])
			: '';
	};

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="event-details__item">
				<div className="event-details__icon">
					<i className="material-icons material-symbols-outlined">event_busy</i>
				</div>
				<div>
					<h4 className="event-details_title">
						{bookingEnded
							? __('Booking ended', 'events')
							: bookingStarted
								? __('Booking end', 'events')
								: __('Booking start', 'events')}
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
