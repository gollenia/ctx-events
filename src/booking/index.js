/*
 *   External dependecies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useEffect, useReducer } from 'react';
import { STATES } from './modules/constants.js';

import './style.scss';

/*
 *   Internal dependecies
 */
import AwaitResponse from './AwaitResponse.js';
import WizardControls from './WizardControls.js';
import WizardGuide from './WizardGuide.js';
import WizardStep from './WizardStep.js';
import Wizard from './WizardSteps.js';
import { subscribe } from './modules/events.js';
import initialState from './modules/initialState.js';
import reducer from './modules/reducer.js';
import Payment from './payment';
import Success from './success';
import TicketList from './ticketList';
import UserRegistration from './userRegistration';

// this function  is suposed to open the modal from the parent component

const Booking = ( { post, open } ) => {
	// if no spaces are left, nothing is shown

	const [ state, dispatch ] = useReducer( reducer, initialState );

	const { wizard, modal, event, request, response } = state;

	console.log( state );

	useEffect( () => {
		const abortController = new AbortController();
		const { signal } = abortController;
		apiFetch( {
			path: `/events/v2/event/${ post }?fields=forms,event,tickets,gateways`,
		} )
			.then( ( data ) => {
				console.log( data );
				dispatch( { type: 'SET_EVENT', payload: data } );
				dispatch( { type: 'SET_INIT_STATE', payload: STATES.LOADING } );
			} )
			.catch( ( error ) => {
				dispatch( { type: 'SET_INIT_STATE', payload: STATES.ERROR } );
			} );
		subscribe( 'showBooking', ( state ) => dispatch( { type: 'SET_MODAL', payload: state } ) );
	}, [] );

	useEffect( () => {
		if ( ! event ) return;
		if ( ! wizard.checkValidity ) return;
		dispatch( {
			type: 'VALIDITY',
			payload: {
				tickets: document.getElementById( 'user-attendee-form' )?.checkValidity() && request.tickets.length > 0,
				registration:
					document.getElementById( 'user-registration-form' )?.checkValidity() && request.tickets.length > 0,
				payment:
					! event?.l10n?.consent || ( event?.l10n?.consent && request.registration.data_privacy_consent ),
			},
		} );
	}, [ state ] );

	if ( modal.initialState == STATES.ERROR )
		return (
			<div className="alert alert--error">{ __( 'An error occured. Please try again later.', 'events' ) }</div>
		);

	if ( ! event ) return <></>;

	return (
		<div>
			<div className={ `event-modal ${ modal.visible ? 'event-modal--open' : '' }` }>
				<div className="event-modal-dialog">
					<div className="event-modal-header">
						<div className="event-modal-caption">
							<div className="">
								<b className="margin--0">{ __( 'Booking', 'events' ) }</b>
								<h3 className="margin--0">{ event?.title }</h3>
							</div>
							<WizardGuide state={ state } />
						</div>
						<button
							className="event-modal-close"
							onClick={ () => {
								dispatch( { type: 'SET_MODAL', payload: false } );
								open = false;
							} }
						></button>
					</div>

					{ modal.orderState > STATES.LOADING ? (
						<>
							<div className="event-modal-content">
								<AwaitResponse state={ state } />
							</div>
							<div className="event-modal-footer"></div>
						</>
					) : (
						<>
							<div className="event-modal-content">
								<Wizard state={ state } dispatch={ dispatch }>
									{ event.forms.attendee_fields?.length > 0 && (
										<WizardStep
											valid={ wizard.steps.tickets.valid }
											invalidMessage={
												request.tickets.length == 0
													? __( 'Please select at least one ticket', 'events' )
													: __( 'Please fill out all required fields', 'events' )
											}
										>
											<TicketList { ...{ state, dispatch } } />
										</WizardStep>
									) }
									<WizardStep
										valid={ wizard.steps.registration.valid }
										invalidMessage={ __( 'Please fill out all required fields', 'events' ) }
									>
										<UserRegistration { ...{ state, dispatch } } />
									</WizardStep>
									<WizardStep valid={ wizard.steps.payment.valid }>
										<Payment { ...{ state, dispatch } } />
									</WizardStep>
									<WizardStep valid={ wizard.steps.success.valid }>
										<Success { ...{ state, dispatch } } />
									</WizardStep>
								</Wizard>
							</div>
							<div className="event-modal-footer">
								<WizardControls state={ state } dispatch={ dispatch } />
							</div>
						</>
					) }
				</div>
			</div>
		</div>
	);
};

export default Booking;
