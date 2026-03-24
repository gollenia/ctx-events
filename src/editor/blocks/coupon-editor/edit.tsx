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
	_code?: string;
	_limit?: number | string;
	_expires_at?: string;
	_type?: 'percent' | 'fixed';
	_value?: number | string;
	_is_global?: boolean;
};

type PostContent = string | { raw?: string };

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
	const [content, setContent] = useEntityProp<PostContent>(
		'postType',
		postType,
		'content',
	);

	const blockProps = useBlockProps({
		className: 'coupon-edit',
	});

	const description = typeof content === 'string' ? content : (content?.raw ?? '');
	const discountType = meta?._type ?? 'percent';

	return (
		<div {...blockProps}>
			<div className="coupon-edit__admin">
				<h3>{__('Coupon Settings', 'ctx-events')}</h3>
				<div className="coupon-edit__code">
					<div className="coupon-edit__code-input">
						<TextControl
							label={__('Coupon Code', 'ctx-events')}
							__next40pxDefaultSize
							value={meta?._code ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_code: value,
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
									_code: Math.random()
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
					value={description}
					onChange={(value) => {
						setContent(
							typeof content === 'string'
								? value
								: { ...(content ?? {}), raw: value },
						);
					}}
				/>
				<h3>{__('Coupon Limits', 'ctx-events')}</h3>
				<Flex>
					<FlexBlock>
						<TextControl
							label={__('Total Coupons', 'ctx-events')}
							type="number"
							__next40pxDefaultSize
							value={meta?._limit ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_limit: value,
								});
							}}
						/>
					</FlexBlock>
					<FlexBlock>
						<TextControl
							label={__('Expiry date', 'ctx-events')}
							type="date"
							__next40pxDefaultSize
							value={meta?._expires_at ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_expires_at: value,
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
							max={discountType === 'percent' ? 100 : undefined}
							min={0}
							__next40pxDefaultSize
							placeholder={discountType === 'percent' ? '0' : '0.00'}
							step={discountType === 'percent' ? 1 : 0.01}
							value={meta?._value ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_value: value,
								});
							}}
						/>
					</FlexBlock>
					<FlexBlock>
						<SelectControl
							options={[
								{
									label: __('Percentage', 'ctx-events'),
									value: 'percent',
								},
								{
									label: __('Fixed Amount', 'ctx-events'),
									value: 'fixed',
								},
							]}
							label={__('Discount Type', 'ctx-events')}
							value={discountType}
							__next40pxDefaultSize
							onChange={(value) => {
								setMeta({
									...meta,
									_type: value as CouponMeta['_type'],
								});
							}}
						/>
					</FlexBlock>
				</Flex>
				<CheckboxControl
					label={__('Activate for all events', 'ctx-events')}
					checked={meta?._is_global ?? false}
					onChange={(value) => {
						setMeta({
							...meta,
							_is_global: value,
						});
					}}
					__next40pxDefaultSize
				/>
			</div>
		</div>
	);
}
