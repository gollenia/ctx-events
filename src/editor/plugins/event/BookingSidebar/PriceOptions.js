import {
	Button,
	CheckboxControl,
	PanelBody,
	SelectControl,
} from '@wordpress/components';
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
			<SelectControl
				label={__('Currency', 'ctx-events')}
				value={meta._booking_currency || 'USD'}
				options={[
					{ label: 'EUR', value: 'EUR' },
					{ label: 'CHF', value: 'CHF' },
					{ label: 'USD', value: 'USD' },
					{ label: 'GBP', value: 'GBP' },
					{ label: 'AUD', value: 'AUD' },
				]}
				onChange={(value) => setMeta({ _booking_currency: value })}
				disabled={!meta._booking_enabled}
			/>
			<CheckboxControl
				label={__('Allow Donation', 'ctx-events')}
				help={__(
					'Allow attendees to donate for other attendees when booking.',
					'ctx-events',
				)}
				checked={meta._event_rsvp_donation}
				onChange={(value) => {
					setMeta({ _event_rsvp_donation: value });
				}}
				disabled={!meta._booking_enabled}
			/>
			<Button
				onClick={() => setShowCoupons(!showCoupons)}
				variant="secondary"
				disabled={!meta._booking_enabled}
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
