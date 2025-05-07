import { Button, Flex, FlexItem, Modal, TextControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import React from 'react';
import CouponRow from './couponRow';

const CouponModal = ( props ) => {
	const { meta, setMeta, showCoupons, setShowCoupons } = props;
	const [ searchTerm, setSearchTerm ] = React.useState( '' );

	const availableCoupons = useSelect( ( select ) => {
		const { getEntityRecords } = select( coreStore );
		const query = { per_page: -1, _embed: true };
		const result = getEntityRecords( 'postType', 'coupon', query );

		if ( ! result ) {
			return [];
		}

		return result.map( ( coupon ) => {
			return {
				id: coupon.id,
				title: coupon.title.raw,
				type: coupon._coupon_type,
				code: coupon._coupon_code,
				expiry: coupon._coupon_expiry,
				amount: coupon._coupon_value,
				fixed: coupon._coupon_sitewide,
			};
		} );
	}, [] );

	const filteredCoupons = availableCoupons.filter( ( coupon ) => {
		return coupon.title.toLowerCase().includes( searchTerm.toLowerCase() );
	} );

	const closeModal = () => {
		setShowCoupons( false );
	};

	const onToggle = ( value, coupon_id ) => {
		let coupons = Array.isArray( meta._event_coupons ) ? [ ...meta._event_coupons ] : [];

		if ( value ) {
			if ( ! coupons.includes( coupon_id ) ) {
				coupons.push( coupon_id );
			}
		} else {
			coupons = coupons.filter( ( id ) => id !== coupon_id );
		}
		setMeta( { _event_coupons: coupons } );
	};

	return (
		<>
			{ showCoupons && (
				<Modal title={ __( 'Select Coupons', 'events' ) } onRequestClose={ closeModal } size="large">
					<TextControl
						label={ __( 'Search Coupons', 'events' ) }
						value={ searchTerm }
						onChange={ ( value ) => setSearchTerm( value ) }
						placeholder={ __( 'Search coupons', 'events' ) }
					/>
					<table className="wp-list-table widefat striped table-view-list posts">
						<thead>
							<tr>
								<th>#</th>
								<th>{ __( 'Name', 'events' ) }</th>
								<th>{ __( 'Code', 'events' ) }</th>
								<th>{ __( 'Amount', 'events' ) }</th>
								<th>{ __( 'Expires', 'events' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ filteredCoupons.map( ( coupon, index ) => {
								const isSelected = meta._event_coupons?.includes( coupon.id );
								return (
									<CouponRow
										coupon={ coupon }
										isSelected={ isSelected }
										index={ index }
										onToggle={ ( value, id ) => onToggle( value, id ) }
									/>
								);
							} ) }
						</tbody>
					</table>
					<Flex justify="flex-end" style={ { marginTop: '1rem' } }>
						<FlexItem>
							<Button
								variant="primary"
								onClick={ () => {
									setShowCoupons( false );
								} }
							>
								{ __( 'Close', 'events' ) }
							</Button>
						</FlexItem>
					</Flex>
				</Modal>
			) }
		</>
	);
};

export default CouponModal;
