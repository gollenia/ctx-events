import { __ } from '@wordpress/i18n';

const Log = ({ booking }) => {
	if (!booking.log) {
		return <p>{__('No log entries found.', 'ctx-events')}</p>;
	}
	return (
		<div className="booking-log">
			<h2>{__('Booking Log', 'ctx-events')}</h2>
			<ul>
				{booking.log.map((entry, index) => (
					<li key={index}>
						<strong>{entry.date}:</strong> {entry.text}
					</li>
				))}
			</ul>
		</div>
	);
};

export default Log;
