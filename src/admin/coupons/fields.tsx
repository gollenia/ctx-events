import { formatPrice } from '@events/i18n';
import type { DataFieldConfig } from '@events/datatable';
import { __ } from '@wordpress/i18n';
import type { CouponListItem } from 'src/types/types';

type FieldCallbacks = {
	onCodeCopy: (code: string) => void;
};

export const createFields = (
	callbacks: FieldCallbacks,
): Array<DataFieldConfig> => [
	{
		id: 'title',
		label: __('Title', 'ctx-events'),
		enableSorting: true,
		render: (coupon: CouponListItem) => (
			<strong>
				<a href={`/wp-admin/post.php?post=${coupon.id}&action=edit`}>
					{coupon.title || __('(No title)', 'ctx-events')}
				</a>
			</strong>
		),
	},
	{
		id: 'code',
		label: __('Code', 'ctx-events'),
		getValue: (coupon: CouponListItem) => coupon.code,
		render: (coupon: CouponListItem) => (
			<a
				href="#"
				onClick={(event) => {
					event.preventDefault();
					callbacks.onCodeCopy(coupon.code);
				}}
			>
				{coupon.code || '—'}
			</a>
		),
	},
	{
		id: 'discount',
		label: __('Discount', 'ctx-events'),
		getValue: (coupon: CouponListItem) =>
			coupon.discountType === 'fixed'
				? formatPrice({
						amountCents: coupon.discountValue,
						currency: 'EUR',
					})
				: `${coupon.discountValue}%`,
	},
	{
		id: 'usage',
		label: __('Usage', 'ctx-events'),
		getValue: (coupon: CouponListItem) =>
			`${coupon.usageCount ?? 0} / ${coupon.usageLimit ?? '∞'}`,
	},
	{
		id: 'validFrom',
		label: __('Valid from', 'ctx-events'),
		getValue: (coupon: CouponListItem) =>
			coupon.validFrom
				? new Date(coupon.validFrom).toLocaleDateString()
				: '—',
	},
	{
		id: 'expiresAt',
		label: __('Expires', 'ctx-events'),
		getValue: (coupon: CouponListItem) =>
			coupon.expiresAt
				? new Date(coupon.expiresAt).toLocaleDateString()
				: '—',
	},
	{
		id: 'status',
		label: __('Status', 'ctx-events'),
		enableSorting: true,
		getValue: (coupon: CouponListItem) => coupon.status,
		filterBy: {
			id: 'status',
			label: __('Status', 'ctx-events'),
			type: 'text',
			elements: [
				{ value: 'publish', label: __('Published', 'ctx-events') },
				{ value: 'draft', label: __('Draft', 'ctx-events') },
				{ value: 'trash', label: __('Trash', 'ctx-events') },
			],
		},
	},
];
