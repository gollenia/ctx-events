import { Button, CheckboxControl, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import CouponModal from './couponModal';

const PriceAdjustments = ( props ) => {
	const [ showCoupons, setShowCoupons ] = React.useState( false );
	const { meta, setMeta } = props;
	return (
		<PanelBody title={ __( 'Price Options', 'events' ) } initialOpen={ true }>
			<CheckboxControl
				label={ __( 'Allow Donation', 'events' ) }
				help={ __( 'Allow attendees to donate for other attendees when booking.', 'events' ) }
				checked={ meta._event_rsvp_donation }
				onChange={ ( value ) => {
					setMeta( { _event_rsvp_donation: value } );
				} }
				disabled={ ! meta._event_rsvp }
			/>
			<Button
				onClick={ () => setShowCoupons( ! showCoupons ) }
				variant="secondary"
				disabled={ ! meta._event_rsvp }
			>
				{ __( 'Select Coupons', 'events' ) }
			</Button>
			<p>
				{ __( 'Currently selected coupons: ', 'events' ) } { meta._event_coupons?.length }
			</p>
			<CouponModal
				{ ...props }
				meta={ meta }
				setMeta={ setMeta }
				showCoupons={ showCoupons }
				setShowCoupons={ setShowCoupons }
			/>
		</PanelBody>
	);
};

export default PriceAdjustments;
