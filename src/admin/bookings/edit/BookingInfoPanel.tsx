import { Flex, FlexItem } from '@contexis/wp-react-form';
import { CurrencyField } from '@events/adminfields';
import { formatPrice } from '@events/i18n';
import { Panel, PanelBody, SelectControl } from '@wordpress/components';
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
	isSaving: boolean;
	onRegistrationChange: (key: string, value: unknown) => void;
	onGatewayChange: (gateway: string | null) => void;
	onDonationChange: (donationCents: number) => void;
	onSave: () => void;
};

const BookingInfoPanel = ({
	booking,
	registration,
	registrationFields,
	availableGateways,
	isSaving,
	onRegistrationChange,
	onGatewayChange,
	onDonationChange,
	onSave,
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

				<Flex gap="1rem" direction="column">
					<FlexItem>
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
					</FlexItem>
					<FlexItem>
						<CurrencyField
							label={__('Donation', 'ctx-events')}
							currency={booking.price.donationAmount.currency}
							value={String(
								(booking.price.donationAmount.amountCents ?? 0) / 100,
							)}
							onChange={(value) => {
								const amount = Number.parseFloat(String(value));
								const donationCents = Number.isFinite(amount)
									? Math.round(amount * 100)
									: 0;

								onDonationChange(donationCents);
							}}
						/>
					</FlexItem>
				</Flex>

				<div className="booking-edit__info-actions">
					<button
						type="button"
						className="components-button is-primary"
						onClick={onSave}
						disabled={isSaving}
						aria-busy={isSaving}
					>
						{isSaving
							? __('Saving…', 'ctx-events')
							: __('Save changes', 'ctx-events')}
					</button>
				</div>
			</PanelBody>
		</Panel>
	);
};

export default BookingInfoPanel;
