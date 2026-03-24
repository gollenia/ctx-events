import { formatPrice } from '@events/i18n';
import { __, sprintf } from '@wordpress/i18n';

import type { CouponCheckResult } from '../types';

type Props = {
	code: string;
	currency: string;
	couponStatus: 'idle' | 'loading' | 'success' | 'error';
	couponResult: CouponCheckResult | null;
	appliedCoupon: CouponCheckResult | null;
	couponMessage: string;
	liveDiscountAmount: number;
	onCodeChange: (code: string) => void;
	onCheck: () => void;
};

export function CouponField({
	code,
	currency,
	couponStatus,
	couponResult,
	appliedCoupon,
	couponMessage,
	liveDiscountAmount,
	onCodeChange,
	onCheck,
}: Props) {
	return (
		<div className="booking-coupon">
			<label className="booking-coupon__label" htmlFor="booking-coupon-code">
				{__('Coupon code', 'ctx-events')}
			</label>
			<input
				id="booking-coupon-code"
				type="text"
				className="booking-coupon__input"
				value={code}
				onChange={(event) => onCodeChange(event.target.value)}
				placeholder={__('Optional', 'ctx-events')}
			/>
			<button
				type="button"
				className="booking-btn booking-btn--secondary booking-coupon__button"
				onClick={onCheck}
				disabled={couponStatus === 'loading' || !code.trim()}
			>
				{couponStatus === 'loading'
					? __('Checking…', 'ctx-events')
					: __('Check coupon', 'ctx-events')}
			</button>
			{couponStatus === 'success' && couponResult && (
				<p className="booking-coupon__message booking-coupon__message--success">
					{sprintf(
						// translators: 1: coupon name, 2: discount amount
						__('Coupon "%1$s" is valid. Discount: %2$s', 'ctx-events'),
						couponResult.name,
						formatPrice({ amountCents: liveDiscountAmount, currency }),
					)}
				</p>
			)}
			{couponStatus !== 'success' && appliedCoupon && (
				<p className="booking-coupon__message booking-coupon__message--success">
					{sprintf(
						// translators: 1: coupon name, 2: discount amount
						__('Coupon "%1$s" applied. Current discount: %2$s', 'ctx-events'),
						appliedCoupon.name,
						formatPrice({ amountCents: liveDiscountAmount, currency }),
					)}
				</p>
			)}
			{couponStatus === 'error' && couponMessage && (
				<p
					className="booking-coupon__message booking-coupon__message--error"
					role="alert"
				>
					{couponMessage}
				</p>
			)}
		</div>
	);
}
