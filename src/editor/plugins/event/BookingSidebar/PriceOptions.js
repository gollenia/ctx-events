import { Button, CheckboxControl, PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import CouponModal from './CouponModal';
import TicketModal from './TicketModal';

const PriceAdjustments = (props) => {
	const [showCoupons, setShowCoupons] = useState(false);
	const [showTickets, setShowTickets] = useState(false);
	const { meta, setMeta } = props;
	return (
		<PanelBody title={__('Price Options', 'ctx-events')} initialOpen={true}>
			<CheckboxControl
				label={__('Allow Donation', 'ctx-events')}
				help={__(
					'Allow attendees to donate for other attendees when booking.',
					'events',
				)}
				checked={meta._event_rsvp_donation}
				onChange={(value) => {
					setMeta({ _event_rsvp_donation: value });
				}}
				disabled={!meta._event_rsvp}
			/>
			<Button
				onClick={() => setShowCoupons(!showCoupons)}
				variant="secondary"
				disabled={!meta._event_rsvp}
			>
				{__('Select Coupons', 'ctx-events')}
			</Button>
			<p>
				{__('Currently selected coupons: ', 'ctx-events')}{' '}
				{meta._event_coupons?.length}
			</p>
			<CouponModal
				{...props}
				meta={meta}
				setMeta={setMeta}
				showCoupons={showCoupons}
				setShowCoupons={setShowCoupons}
			/>
			<Button
				onClick={() => setShowTickets(!showTickets)}
				variant="secondary"
				disabled={!meta._booking_enabled}
			>
				{__('Edit Tickets', 'ctx-events')}
			</Button>
			<TicketModal
				{...props}
				meta={meta}
				setMeta={setMeta}
				showTickets={showTickets}
				setShowTickets={setShowTickets}
			/>
		</PanelBody>
	);
};

export default PriceAdjustments;
