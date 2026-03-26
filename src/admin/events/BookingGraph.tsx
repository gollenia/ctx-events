import { __ } from '@wordpress/i18n';
import type { EventBookingSummary } from '../../types/types';

type Props = {
	bookingSummary: EventBookingSummary;
};

export function BookingGraph({ bookingSummary }: Props) {
	const { approved, pending, totalCapacity } = bookingSummary;

	if (!totalCapacity) {
		return <span>—</span>;
	}

	const bookedPercent = Math.min((approved / totalCapacity) * 100, 100);
	const pendingPercent = Math.min(
		(pending / totalCapacity) * 100,
		100 - bookedPercent,
	);
	const isFull = bookedPercent >= 100;

	return (
		<div>
			<span className="ctx-booking-graph__label">
				{approved} {__('booked', 'ctx-events')} / {pending}{' '}
				{__('pending', 'ctx-events')}
			</span>
			<div
				className="ctx-booking-graph"
				title={`${approved + pending} / ${totalCapacity}`}
			>
				{isFull ? (
					<div className="ctx-booking-graph__full" style={{ width: '100%' }} />
				) : (
					<>
						{bookedPercent > 0 && (
							<div
								className={`ctx-booking-graph__booked${pendingPercent > 0 ? ' cut' : ''}`}
								style={{ width: `${bookedPercent}%` }}
							/>
						)}
						{pendingPercent > 0 && (
							<div
								className={`ctx-booking-graph__pending${bookedPercent > 0 ? ' cut' : ''}`}
								style={{ width: `${pendingPercent}%` }}
							/>
						)}
					</>
				)}
			</div>
		</div>
	);
}
