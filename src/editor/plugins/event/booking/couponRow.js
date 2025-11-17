import { CheckboxControl, Disabled } from '@wordpress/components';
import { formatPrice } from '../../../../shared/formatPrice';

const CouponRow = (props) => {
	const { coupon, index, onToggle, isSelected } = props;

	const price =
		coupon.type === 'fixed'
			? formatPrice(coupon.amount, eventBlocksLocalization.currency)
			: coupon.amount + '%';
	return (
		<tr>
			<td>
				{coupon.fixed ? (
					<Disabled>
						<CheckboxControl
							id={`coupon-${coupon.id}`}
							name={`coupon-${coupon.id}`}
							onChange={(value) => onToggle(value, coupon.id)}
							checked={isSelected || coupon.fixed}
						/>
					</Disabled>
				) : (
					<CheckboxControl
						id={`coupon-${coupon.id}`}
						name={`coupon-${coupon.id}`}
						onChange={(value) => onToggle(value, coupon.id)}
						checked={isSelected || coupon.fixed}
					/>
				)}
			</td>
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
