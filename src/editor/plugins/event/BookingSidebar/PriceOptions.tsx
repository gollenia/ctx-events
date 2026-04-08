import PanelTitle from '@events/adminfields/PanelTitle';
import {
	Button,
	CheckboxControl,
	Flex,
	PanelBody,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import icons from '../icons';
import CouponModal from './CouponModal';
import {
	type BookingSidebarProps,
	getDefaultCurrency,
	isBookingEnabled,
} from './types';

const PriceAdjustments = ({
	meta,
	updateMeta,
	postId,
	postType,
}: BookingSidebarProps) => {
	const [showCoupons, setShowCoupons] = useState(false);

	const enabled = isBookingEnabled(meta);
	const selectedCoupons = meta._booking_coupons?.length ?? 0;

	return (
		<PanelBody
			title={
				<PanelTitle icon={icons.payment} title={__('Payment', 'ctx-events')} />
			}
			initialOpen={true}
		>
			<Flex gap={4} direction="column">
				<SelectControl
					label={__('Currency', 'ctx-events')}
					value={meta._booking_currency ?? getDefaultCurrency()}
					options={[
						{ label: 'EUR', value: 'EUR' },
						{ label: 'CHF', value: 'CHF' },
						{ label: 'USD', value: 'USD' },
						{ label: 'GBP', value: 'GBP' },
						{ label: 'AUD', value: 'AUD' },
					]}
					onChange={(value) => updateMeta({ _booking_currency: value })}
					disabled={!enabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<CheckboxControl
					label={__('Allow Donation', 'ctx-events')}
					help={__(
						'Allow attendees to donate for other attendees when booking.',
						'ctx-events',
					)}
					checked={Boolean(meta._donation_enabled)}
					onChange={(value) => {
						updateMeta({ _donation_enabled: value });
					}}
					disabled={!enabled}
					__nextHasNoMarginBottom
				/>
				<TextControl
					label={__('Booking reference prefix', 'ctx-events')}
					value={meta._booking_reference_prefix ?? ''}
					onChange={(value) => updateMeta({ _booking_reference_prefix: value })}
					disabled={!enabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<TextControl
					label={__('Booking reference suffix', 'ctx-events')}
					value={meta._booking_reference_suffix ?? ''}
					onChange={(value) => updateMeta({ _booking_reference_suffix: value })}
					disabled={!enabled}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
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
			</Flex>
		</PanelBody>
	);
};

export default PriceAdjustments;
