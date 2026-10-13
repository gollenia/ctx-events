import { formatPrice } from '@events/i18n';
import {
	Button,
	CheckboxControl,
	Disabled,
	Flex,
	FlexItem,
	Modal,
	TextControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SmallDataTable, {
	type SmallDataTableField,
} from '../../../../shared/small-data-table';

import { type BookingSidebarProps, getDefaultCurrency } from './types';

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

type CouponModalProps = BookingSidebarProps & {
	showCoupons: boolean;
	setShowCoupons: (value: boolean) => void;
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
	const fields: SmallDataTableField<Coupon>[] = [
		{
			id: 'selected',
			ariaLabel: __('Selected', 'ctx-events'),
			render: (coupon) => {
				const checkbox = (
					<CheckboxControl
						id={`coupon-${coupon.id}`}
						name={`coupon-${coupon.id}`}
						onChange={(value) => onToggle(value, coupon.id)}
						checked={selectedCoupons.includes(coupon.id) || coupon.fixed}
					/>
				);
				return coupon.fixed ? <Disabled>{checkbox}</Disabled> : checkbox;
			},
		},
		{
			id: 'name',
			label: __('Name', 'ctx-events'),
			render: (coupon) => <b>{coupon.title}</b>,
		},
		{
			id: 'code',
			label: __('Code', 'ctx-events'),
			render: (coupon) => coupon.code,
		},
		{
			id: 'amount',
			label: __('Amount', 'ctx-events'),
			render: (coupon) =>
				coupon.type === 'fixed'
					? formatPrice(coupon.amount, getDefaultCurrency())
					: `${coupon.amount}%`,
		},
		{
			id: 'expires',
			label: __('Expires', 'ctx-events'),
			render: (coupon) => coupon.expiry,
		},
	];

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
			<SmallDataTable
				items={filteredCoupons}
				fields={fields}
				getRowKey={(coupon) => coupon.id}
				className="wp-list-table widefat striped table-view-list posts"
			/>
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
