import { formatPrice } from '@events/i18n';
import { Panel, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { BookingDetail } from 'src/types/types';
import { STATUS_LABELS } from '../constants';
import DynamicFieldsGrid from './DynamicFieldsGrid';
import type { BookingFormValues } from './formFields';

type Props = {
	booking: BookingDetail;
	registration: BookingFormValues;
	registrationFields: BookingDetail['bookingForm']['fields'];
	availableGateways: Array<{ value: string; label: string }>;
	onRegistrationChange: (key: string, value: unknown) => void;
	onGatewayChange: (gateway: string | null) => void;
	onDonationChange: (donationCents: number) => void;
};

const BookingInfoPanel = ({
	booking,
	registration,
	registrationFields,
	availableGateways,
	onRegistrationChange,
	onGatewayChange,
	onDonationChange,
}: Props) => {
	return (
		<Panel header={__('Booking Info', 'ctx-events')}>
			<PanelBody>
				<div className="booking-edit__meta">
					<span>
						<strong>{__('Status', 'ctx-events')}:</strong>{' '}
						{STATUS_LABELS[booking.status] ?? booking.status}
					</span>
					<span>
						<strong>{__('Date', 'ctx-events')}:</strong>{' '}
						{new Date(booking.date).toLocaleString()}
					</span>
					<span>
						<strong>{__('Total', 'ctx-events')}:</strong>{' '}
						{formatPrice(booking.price.finalPrice)}
					</span>
				</div>

				<DynamicFieldsGrid
					fields={registrationFields}
					values={registration}
					onChange={onRegistrationChange}
					gridClassName="booking-edit__registration-grid"
					fieldClassName="booking-edit__registration-grid-field"
					inputWrapClassName="booking-edit__field-input-wrap"
				/>

				<SelectControl
					label={__('Gateway', 'ctx-events')}
					value={booking.gateway ?? ''}
					options={[
						{ value: '', label: __('— None —', 'ctx-events') },
						...availableGateways,
					]}
					onChange={(value) => onGatewayChange(value || null)}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>

				<TextControl
					label={__('Donation', 'ctx-events')}
					type="number"
					value={String((booking.price.donationAmount.amountCents ?? 0) / 100)}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					onChange={(value) => {
						const amount = Number.parseFloat(value);
						const donationCents = Number.isFinite(amount)
							? Math.round(amount * 100)
							: 0;

						onDonationChange(donationCents);
					}}
				/>
			</PanelBody>
		</Panel>
	);
};

export default BookingInfoPanel;
