/**
 * Wordpress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { Button, CheckboxControl, Flex, FlexBlock, SelectControl, TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import 'leaflet/dist/leaflet.css';
/**
 * Internal dependencies
 */

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = ( props ) => {
	const postType = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostType(), [] );
	if ( postType !== 'coupon' ) return <></>;
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	const blockProps = useBlockProps( {
		className: [ 'coupon-edit' ].filter( Boolean ).join( ' ' ),
	} );

	return (
		<div { ...blockProps }>
			<div className="coupon-edit__admin">
				<h3>{ __( 'Coupon Settings', 'events' ) }</h3>
				<div className="coupon-edit__code">
					<div className="coupon-edit__code-input">
						<TextControl
							label={ __( 'Coupon Code', 'events' ) }
							__next40pxDefaultSize
							value={ meta._coupon_code }
							onChange={ ( value ) => {
								setMeta( {
									...meta,
									_coupon_code: value,
								} );
							} }
							className="code"
						/>
					</div>
					<div className="coupon-edit__code-generate">
						<Button
							variant="secondary"
							__next40pxDefaultSize
							onClick={ () => {
								setMeta( {
									...meta,
									_coupon_code: Math.random().toString( 36 ).slice( 2, 12 ).toUpperCase(),
								} );
							} }
							className="generate"
						>
							{ __( 'Generate', 'events' ) }
						</Button>
					</div>
				</div>
				<TextControl
					label={ __( 'Description', 'events' ) }
					__next40pxDefaultSize
					value={ meta._coupon_description }
					onChange={ ( value ) => {
						setMeta( {
							...meta,
							_coupon_description: value,
						} );
					} }
				/>
				<h3>{ __( 'Coupon Limits', 'events' ) }</h3>
				<Flex>
					<FlexBlock>
						<TextControl
							label={ __( 'Total Coupons', 'events' ) }
							type="number"
							__next40pxDefaultSize
							value={ meta._coupon_limit }
							onChange={ ( value ) => {
								setMeta( {
									...meta,
									_coupon_limit: value,
								} );
							} }
						/>
					</FlexBlock>
					<FlexBlock>
						<TextControl
							label={ __( 'Expiry date', 'events' ) }
							type="date"
							__next40pxDefaultSize
							value={ meta._coupon_expiry }
							onChange={ ( value ) => {
								setMeta( {
									...meta,
									_coupon_expiry: value,
								} );
							} }
						/>
					</FlexBlock>
				</Flex>
				<h3>{ __( 'Discount', 'events' ) }</h3>
				<Flex>
					<FlexBlock>
						<TextControl
							label={ __( 'Discount Amount', 'events' ) }
							type="number"
							max={ meta._coupon_type === 'percentage' ? 100 : undefined }
							min={ 0 }
							__next40pxDefaultSize
							placeholder={ meta._coupon_type === 'percentage' ? '0' : '0.00' }
							step={ meta._coupon_type === 'percentage' ? 1 : 0.01 }
							value={ meta._coupon_value }
							onChange={ ( value ) => {
								setMeta( {
									...meta,
									_coupon_value: value,
								} );
							} }
						/>
					</FlexBlock>
					<FlexBlock>
						<SelectControl
							options={ [
								{
									label: __( 'Percentage', 'events' ),
									value: 'percentage',
								},
								{
									label: __( 'Fixed Amount', 'events' ),
									value: 'fixed',
								},
							] }
							label={ __( 'Discount Type', 'events' ) }
							value={ meta._coupon_type }
							__next40pxDefaultSize
							onChange={ ( value ) => {
								setMeta( {
									...meta,
									_coupon_type: value,
								} );
							} }
						/>
					</FlexBlock>
				</Flex>
				<CheckboxControl
					label={ __( 'Activate for all events', 'events' ) }
					checked={ meta._coupon_sitewide }
					onChange={ ( value ) => {
						setMeta( {
							...meta,
							_coupon_sitewide: value,
						} );
					} }
					__next40pxDefaultSize
				/>
			</div>
		</div>
	);
};

export default edit;
