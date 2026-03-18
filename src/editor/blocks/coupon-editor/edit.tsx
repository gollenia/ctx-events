import { useBlockProps } from '@wordpress/block-editor';
import {
	Button,
	CheckboxControl,
	Flex,
	FlexBlock,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

type CouponMeta = {
	_coupon_code?: string;
	_coupon_description?: string;
	_coupon_limit?: number | string;
	_coupon_expiry?: string;
	_coupon_type?: 'percentage' | 'fixed';
	_coupon_value?: number | string;
	_coupon_global?: boolean;
};

type EditProps = {
	context?: {
		postType?: string;
	};
};

export default function Edit(props: EditProps) {
	const postType = props.context?.postType;
	if (postType !== 'ctx-event-coupon') {
		return null;
	}

	const [meta, setMeta] = useEntityProp<CouponMeta>(
		'postType',
		postType,
		'meta',
	);

	const blockProps = useBlockProps({
		className: 'coupon-edit',
	});

	return (
		<div {...blockProps}>
			<div className="coupon-edit__admin">
				<h3>{__('Coupon Settings', 'ctx-events')}</h3>
				<div className="coupon-edit__code">
					<div className="coupon-edit__code-input">
						<TextControl
							label={__('Coupon Code', 'ctx-events')}
							__next40pxDefaultSize
							value={meta?._coupon_code ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_coupon_code: value,
								});
							}}
							className="code"
						/>
					</div>
					<div className="coupon-edit__code-generate">
						<Button
							variant="secondary"
							__next40pxDefaultSize
							onClick={() => {
								setMeta({
									...meta,
									_coupon_code: Math.random()
										.toString(36)
										.slice(2, 12)
										.toUpperCase(),
								});
							}}
							className="generate"
						>
							{__('Generate', 'ctx-events')}
						</Button>
					</div>
				</div>
				<TextControl
					label={__('Description', 'ctx-events')}
					__next40pxDefaultSize
					value={meta?._coupon_description ?? ''}
					onChange={(value) => {
						setMeta({
							...meta,
							_coupon_description: value,
						});
					}}
				/>
				<h3>{__('Coupon Limits', 'ctx-events')}</h3>
				<Flex>
					<FlexBlock>
						<TextControl
							label={__('Total Coupons', 'ctx-events')}
							type="number"
							__next40pxDefaultSize
							value={meta?._coupon_limit ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_coupon_limit: value,
								});
							}}
						/>
					</FlexBlock>
					<FlexBlock>
						<TextControl
							label={__('Expiry date', 'ctx-events')}
							type="date"
							__next40pxDefaultSize
							value={meta?._coupon_expiry ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_coupon_expiry: value,
								});
							}}
						/>
					</FlexBlock>
				</Flex>
				<h3>{__('Discount', 'ctx-events')}</h3>
				<Flex>
					<FlexBlock>
						<TextControl
							label={__('Discount Amount', 'ctx-events')}
							type="number"
							max={meta?._coupon_type === 'percentage' ? 100 : undefined}
							min={0}
							__next40pxDefaultSize
							placeholder={meta?._coupon_type === 'percentage' ? '0' : '0.00'}
							step={meta?._coupon_type === 'percentage' ? 1 : 0.01}
							value={meta?._coupon_value ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_coupon_value: value,
								});
							}}
						/>
					</FlexBlock>
					<FlexBlock>
						<SelectControl
							options={[
								{
									label: __('Percentage', 'ctx-events'),
									value: 'percentage',
								},
								{
									label: __('Fixed Amount', 'ctx-events'),
									value: 'fixed',
								},
							]}
							label={__('Discount Type', 'ctx-events')}
							value={meta?._coupon_type ?? 'percentage'}
							__next40pxDefaultSize
							onChange={(value) => {
								setMeta({
									...meta,
									_coupon_type: value as CouponMeta['_coupon_type'],
								});
							}}
						/>
					</FlexBlock>
				</Flex>
				<CheckboxControl
					label={__('Activate for all events', 'ctx-events')}
					checked={meta?._coupon_global ?? false}
					onChange={(value) => {
						setMeta({
							...meta,
							_coupon_global: value,
						});
					}}
					__next40pxDefaultSize
				/>
			</div>
		</div>
	);
}
