import { useEffect, useId, useState } from '@wordpress/element';
import { Collapsible } from '@base-ui/react/collapsible';
import { formatPrice } from '@events/i18n';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Flex, InputField } from '../../../shared/__experimentalForm';
import Chevron from '../../../shared/icons/Chevron';
import Plus from '../../../shared/icons/Plus';
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
	const inputId = useId();
	const [open, setOpen] = useState<boolean>(
		Boolean(appliedCoupon || couponStatus === 'error' || code.trim()),
	);

	useEffect(() => {
		if (appliedCoupon || couponStatus === 'error' || couponStatus === 'success') {
			setOpen(true);
		}
	}, [appliedCoupon, couponStatus]);

	return (
		<Collapsible.Root
			className="booking-coupon"
			open={open}
			onOpenChange={setOpen}
			data-testid="booking-coupon"
		>
			<Collapsible.Trigger className="booking-coupon__trigger">
				<span className="booking-coupon__trigger-title-wrap">
					<Plus className="booking-coupon__trigger-icon" />
					<span className="booking-coupon__trigger-title">
						{__('Apply discount code', 'ctx-events')}
					</span>
				</span>
				<Chevron className="booking-coupon__trigger-chevron" open={open} />
			</Collapsible.Trigger>

			<Collapsible.Panel className="booking-coupon__panel">
				<div className="booking-coupon__panel-inner">
					<label className="booking-coupon__label" htmlFor={inputId}>
						{__('Coupon code', 'ctx-events')}
					</label>
					<Flex
						className="booking-coupon__controls"
						align="flex-end"
						gap="0.75rem"
					>
						<div className="booking-coupon__input-field">
							<InputField
								type="text"
								name={`coupon-${inputId}`}
								width={6}
								value={code}
								status="LOADED"
								formTouched={false}
								disabled={false}
								onChange={(value) => onCodeChange(String(value))}
								placeholder={__('Optional', 'ctx-events')}
							/>
						</div>
						<Button
							variant="secondary"
							className="booking-coupon__button"
							onClick={onCheck}
							disabled={couponStatus === 'loading' || !code.trim()}
						>
							{couponStatus === 'loading'
								? __('Checking…', 'ctx-events')
								: __('Check coupon', 'ctx-events')}
						</Button>
					</Flex>
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
			</Collapsible.Panel>
		</Collapsible.Root>
	);
}
