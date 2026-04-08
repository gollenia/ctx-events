import {
	Button,
	Flex,
	FlexItem,
	Modal,
	TextControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import CouponRow from './CouponRow';
import type { BookingSidebarProps } from './types';

type CouponRecord = {
	id: number;
	title: {
		raw?: string;
	};
	meta?: {
		_code?: string;
		_type?: string;
		_value?: string | number;
		_expires_at?: string;
		_is_global?: boolean;
		_coupon_code?: string;
		_coupon_type?: string;
		_coupon_value?: string | number;
		_coupon_expiry?: string;
		_coupon_global?: boolean;
	};
};

type Coupon = {
	id: number;
	title: string;
	type: string;
	code: string;
	expiry: string;
	amount: string | number;
	fixed: boolean;
};

type CouponModalProps = BookingSidebarProps & {
	showCoupons: boolean;
	setShowCoupons: (value: boolean) => void;
};

const CouponModal = ({
	meta,
	updateMeta,
	showCoupons,
	setShowCoupons,
}: CouponModalProps) => {
	const [searchTerm, setSearchTerm] = useState('');

	const availableCoupons = useSelect((select) => {
		const { getEntityRecords } = select(coreStore);
		const result = (getEntityRecords('postType', 'ctx-event-coupon', {
			per_page: -1,
			_embed: true,
		}) ?? []) as CouponRecord[];

		return result.map((coupon) => ({
			id: coupon.id,
			title: coupon.title?.raw ?? '',
			type: coupon.meta?._type ?? coupon.meta?._coupon_type ?? '',
			code: coupon.meta?._code ?? coupon.meta?._coupon_code ?? '',
			expiry: coupon.meta?._expires_at ?? coupon.meta?._coupon_expiry ?? '',
			amount: coupon.meta?._value ?? coupon.meta?._coupon_value ?? '',
			fixed: Boolean(
				coupon.meta?._is_global ?? coupon.meta?._coupon_global ?? false,
			),
		}));
	}, []);

	const filteredCoupons = availableCoupons.filter((coupon) =>
		coupon.title.toLowerCase().includes(searchTerm.toLowerCase()),
	);

	const selectedCoupons = meta._booking_coupons ?? [];

	const onToggle = (value: boolean, couponId: number) => {
		const coupons = value
			? Array.from(new Set([...selectedCoupons, couponId]))
			: selectedCoupons.filter((id) => id !== couponId);

		updateMeta({ _booking_coupons: coupons });
	};

	if (!showCoupons) {
		return null;
	}

	return (
		<Modal
			title={__('Select Coupons', 'ctx-events')}
			onRequestClose={() => setShowCoupons(false)}
			size="large"
		>
			<TextControl
				label={__('Search Coupons', 'ctx-events')}
				value={searchTerm}
				onChange={(value) => setSearchTerm(value)}
				placeholder={__('Search coupons', 'ctx-events')}
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>
			<table className="wp-list-table widefat striped table-view-list posts">
				<thead>
					<tr>
						<th aria-label={__('Selected', 'ctx-events')} />
						<th>{__('Name', 'ctx-events')}</th>
						<th>{__('Code', 'ctx-events')}</th>
						<th>{__('Amount', 'ctx-events')}</th>
						<th>{__('Expires', 'ctx-events')}</th>
					</tr>
				</thead>
				<tbody>
					{filteredCoupons.map((coupon) => (
						<CouponRow
							key={coupon.id}
							coupon={coupon}
							isSelected={selectedCoupons.includes(coupon.id)}
							onToggle={onToggle}
						/>
					))}
				</tbody>
			</table>
			<Flex justify="flex-end" style={{ marginTop: '1rem' }}>
				<FlexItem>
					<Button variant="primary" onClick={() => setShowCoupons(false)}>
						{__('Close', 'ctx-events')}
					</Button>
				</FlexItem>
			</Flex>
		</Modal>
	);
};

export default CouponModal;
