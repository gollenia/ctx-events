import { __ } from '@wordpress/i18n';
import type { BookingDetail, BookingLogEntryResource } from 'src/types/types';

type Props = {
	booking: BookingDetail;
};

const EVENT_LABELS: Record<BookingLogEntryResource['eventType'], string> = {
	created: __('Created', 'ctx-events'),
	updated: __('Updated', 'ctx-events'),
	deleted: __('Deleted', 'ctx-events'),
	approved: __('Approved', 'ctx-events'),
	rejected: __('Rejected', 'ctx-events'),
	cancelled: __('Cancelled', 'ctx-events'),
	restored: __('Restored', 'ctx-events'),
};

const LogEntriesSection = ({ booking }: Props) => {
	const logEntries = [...booking.logEntries].reverse();

	return (
		<section className="booking-edit__section">
			<h3>{__('Activity', 'ctx-events')}</h3>

			{logEntries.length === 0 ? (
				<p className="booking-edit__empty">{__('No activity yet.', 'ctx-events')}</p>
			) : (
				<ul className="booking-edit__activity-list">
					{logEntries.map((entry, index) => (
						<li
							key={`${entry.timestamp}-${entry.eventType}-${index}`}
							className="booking-edit__activity-item"
						>
							<div className="booking-edit__activity-head">
								<strong>{EVENT_LABELS[entry.eventType] ?? entry.eventType}</strong>
								<span>{new Date(entry.timestamp).toLocaleString()}</span>
							</div>
							<p className="booking-edit__activity-actor">
								{entry.actorName || __('Guest', 'ctx-events')}
							</p>
						</li>
					))}
				</ul>
			)}
		</section>
	);
};

export default LogEntriesSection;
