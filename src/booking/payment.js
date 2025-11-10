const { React, useMemo } = require( 'react' );
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import Coupon from './coupon';

import { useState } from 'react';

import { InputField } from '@contexis/wp-react-form';
import Summary from './Summary';

const Payment = ( props ) => {
	const {
		state: { request, response, event },
		state,
		dispatch,
	} = props;

	const [ allowDonation, setAllowDonation ] = useState( false );

	const gatewayOptions = useMemo( () => {
		if ( ! Array.isArray( event?.gateways_available ) ) return {};

		return event.gateways_available.reduce( ( acc, gateway ) => {
			if ( gateway?.slug && gateway?.title ) {
				acc[ gateway.slug ] = gateway.title;
			}
			return acc;
		}, {} );
	}, [ event?.gateways_available ] );

	return (
		<div className="grid xl:grid--columns-2 grid--gap-12">
			<div>
				<Summary state={ state } dispatch={ dispatch } />
			</div>
			<div>
				<form className="form--trap form grid xl:grid--columns-6 grid--gap-8">
					{ event?.has_coupons && <Coupon state={ state } dispatch={ dispatch } /> }
					{ event.gateways_available.length > 1 && (
						<InputField
							label={ __( 'Payment Method', 'events' ) }
							options={ gatewayOptions }
							name={ 'gateway' }
							onChange={ ( event ) => {
								dispatch( { type: 'SET_GATEWAY', payload: event } );
							} }
							type={ 'select' }
							value={ request.gateway }
							locale={ window.eventBookingLocalization.locale }
						/>
					) }
					{ event.allow_donation && window.eventBookingLocalization.donation != '' && (
						<div className="donation" style={ { gridColumn: 'span 6' } }>
							<h4>{ __( 'Donation', 'events' ) }</h4>
							<p dangerouslySetInnerHTML={ { __html: window.eventBookingLocalization.donation } }></p>
							<InputField
								onChange={ ( value ) => {
									if ( ! value ) {
										dispatch( {
											type: 'SET_FIELD',
											payload: { form: 'donation', value: { amount: 0.0 } },
										} );
									}
									setAllowDonation( value );
								} }
								type={ 'checkbox' }
								value={ allowDonation }
								name={ 'allow_donation' }
								help={ __( 'Yes, I want to donate', 'events' ) }
								locale={ event.l10n.locale }
								className="donation-checkbox"
							/>
							<InputField
								label={ __( 'Amount', 'events' ) }
								onChange={ ( event ) => {
									dispatch( {
										type: 'SET_FIELD',
										payload: { form: 'donation', value: parseFloat( event ) },
									} );
								} }
								type={ 'currency' }
								min={ 0.0 }
								max={ 1000.0 }
								step="any"
								disabled={ ! allowDonation }
								value={ request.donation.amount }
								name={ 'donation' }
								locale={ window.eventBookingLocalization.locale }
							/>
						</div>
					) }
					{ window.eventBookingLocalization.consent != '' && (
						<InputField
							onChange={ ( event ) => {
								dispatch( {
									type: 'SET_FIELD',
									payload: { form: 'registration', field: 'data_privacy_consent', value: event },
								} );
							} }
							type={ 'checkbox' }
							value={ request.registration.data_privacy_consent }
							name={ 'data_privacy_consent' }
							help={ window.eventBookingLocalization.consent }
							locale={ window.eventBookingLocalization.locale }
						/>
					) }

					{ response.error != '' && (
						<div
							class="alert bg-error text-white"
							dangerouslySetInnerHTML={ { __html: response.error } }
						></div>
					) }
				</form>
			</div>
		</div>
	);
};

Payment.propTypes = {
	gateways: PropTypes.array,
	coupons: PropTypes.array,
	eventData: PropTypes.object,
	nonce: PropTypes.string,
};

export default Payment;
