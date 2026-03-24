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
import { isBookingEnabled, type BookingSidebarProps } from './types';

const PriceAdjustments = ({ meta, updateMeta, postId, postType }: BookingSidebarProps) => {
	const [showCoupons, setShowCoupons] = useState(false);
	const [showTickets, setShowTickets] = useState(false);
	const enabled = isBookingEnabled(meta);
	const selectedCoupons = meta._booking_coupons?.length ?? 0;

	return (
		<PanelBody title={__('Price Options', 'ctx-events')} initialOpen={true}>
			<SelectControl
				label={__('Currency', 'ctx-events')}
				value={meta._booking_currency ?? 'USD'}
				options={[
					{ label: 'EUR', value: 'EUR' },
					{ label: 'CHF', value: 'CHF' },
					{ label: 'USD', value: 'USD' },
					{ label: 'GBP', value: 'GBP' },
					{ label: 'AUD', value: 'AUD' },
				]}
				onChange={(value) => updateMeta({ _booking_currency: value })}
				disabled={!enabled}
			/>
			<CheckboxControl
				label={__('Allow Donation', 'ctx-events')}
				help={__(
					'Allow attendees to donate for other attendees when booking.',
					'ctx-events',
				)}
				checked={Boolean(meta._event_rsvp_donation)}
				onChange={(value) => {
					updateMeta({ _event_rsvp_donation: value });
				}}
				disabled={!enabled}
			/>
			<Button
				onClick={() => setShowCoupons(true)}
				variant="secondary"
				disabled={!enabled}
			>
				{__('Select Coupons', 'ctx-events')}
			</Button>
			<p>
				{__('Currently selected coupons:', 'ctx-events')} {selectedCoupons}
			</p>
			<CouponModal
				meta={meta}
				updateMeta={updateMeta}
				postId={postId}
				postType={postType}
				showCoupons={showCoupons}
				setShowCoupons={setShowCoupons}
			/>
			<Button
				onClick={() => setShowTickets(true)}
				variant="secondary"
				disabled={!enabled}
			>
				{__('Edit Tickets', 'ctx-events')}
			</Button>
			<TicketModal
				meta={meta}
				updateMeta={updateMeta}
				postId={postId}
				postType={postType}
				showTickets={showTickets}
				setShowTickets={setShowTickets}
			/>
		</PanelBody>
	);
};

export default PriceAdjustments;
