import { CheckboxControl, Disabled } from '@wordpress/components';

import { formatPrice } from '@events/i18n';

import { getEventEditorLocalization } from './types';

type Coupon = {
	id: number;
	title: string;
	type: string;
	code: string;
	expiry: string;
	amount: string | number;
	fixed: boolean;
};

type CouponRowProps = {
	coupon: Coupon;
	isSelected: boolean;
	onToggle: (value: boolean, couponId: number) => void;
};

const CouponRow = ({ coupon, isSelected, onToggle }: CouponRowProps) => {
	const currency = getEventEditorLocalization().currency ?? 'USD';
	const price =
		coupon.type === 'fixed'
			? formatPrice(coupon.amount, currency)
			: `${coupon.amount}%`;

	const checkbox = (
		<CheckboxControl
			id={`coupon-${coupon.id}`}
			name={`coupon-${coupon.id}`}
			onChange={(value) => onToggle(value, coupon.id)}
			checked={isSelected || coupon.fixed}
		/>
	);

	return (
		<tr>
			<td>{coupon.fixed ? <Disabled>{checkbox}</Disabled> : checkbox}</td>
			<td>
				<b>{coupon.title}</b>
			</td>
			<td>{coupon.code}</td>
			<td>{price}</td>
			<td>{coupon.expiry}</td>
		</tr>
	);
};

export default CouponRow;
