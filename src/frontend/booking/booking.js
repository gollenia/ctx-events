/*
 *   External dependecies
 */
import { useEffect, useReducer } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { STATES } from './modules/constants.js';
import './style.scss';

/*
 *   Internal dependecies
 */
import useApiFetch from '@contexis/use-api-fetch';
import Attendees from './Attendees.js';
import AwaitResponse from './AwaitResponse.js';
import { subscribe } from './modules/events.js';
import initialState from './modules/initialState.js';
import reducer from './modules/reducer.js';
import Payment from './payment';
import Success from './success';
import UserRegistration from './userRegistration';
import WizardControls from './WizardControls.js';
import WizardGuide from './WizardGuide.js';
import WizardStep from './WizardStep.js';
import Wizard from './WizardSteps.js';

// this function  is suposed to open the modal from the parent component

const Booking = ({ post, open }) => {
	// if no spaces are left, nothing is shown

	const [state, dispatch] = useReducer(reducer, initialState);

	const { wizard, modal, event, request, response } = state;

	const { result, error, loading } = useApiFetch(`/events/v2/event/`, {
		urlParams: {
			id: post,
			fields: ['forms', 'event', 'date', 'gateways', 'tickets'],
		},
	});

	console.log('state', state);
	useEffect(() => {
		if (result) {
			dispatch({ type: 'SET_EVENT', payload: result });
			dispatch({ type: 'SET_INIT_STATE', payload: STATES.LOADING });
			console.log('Event loaded', result);
			subscribe('showBooking', (state) =>
				dispatch({ type: 'SET_MODAL', payload: state }),
			);
		}
	}, [result]);

	useEffect(() => {
		dispatch({
			type: 'VALIDITY',
			payload: {
				tickets:
					document.getElementById('user-attendee-form')?.checkValidity() &&
					request.attendees.length > 0,
				registration:
					document.getElementById('user-registration-form')?.checkValidity() &&
					request.attendees.length > 0,
				payment:
					!event?.l10n?.consent ||
					(event?.l10n?.consent && request.registration.data_privacy_consent),
			},
		});
	}, [state.wizard.checkValidityToken]);

	if (modal.initialState === STATES.ERROR)
		return (
			<div className="alert alert--error">
				{__('An error occured. Please try again later.', 'events')}
			</div>
		);

	if (!event) return null;

	return (
		<div>
			<div
				className={`event-modal ${modal.visible ? 'event-modal--open' : ''}`}
			>
				<div className="event-modal-dialog">
					<div className="event-modal-header">
						<div className="event-modal-caption">
							<div className="">
								<b className="margin--0">{__('Booking', 'events')}</b>
								<h3 className="margin--0">{event?.title}</h3>
							</div>
							<WizardGuide state={state} />
						</div>
						<button
							type="button"
							className="event-modal-close"
							onClick={() => {
								dispatch({ type: 'SET_MODAL', payload: false });
								open = false;
							}}
						></button>
					</div>

					{modal.orderState > STATES.LOADING ? (
						<>
							<div className="event-modal-content">
								<AwaitResponse state={state} />
							</div>
							<div className="event-modal-footer"></div>
						</>
					) : (
						<>
							<div className="event-modal-content">
								<Wizard state={state} dispatch={dispatch}>
									{event.forms.attendee_fields?.length > 0 && (
										<WizardStep
											valid={wizard.steps.tickets.valid}
											invalidMessage={
												request.attendees.length == 0
													? __('Please select at least one ticket', 'events')
													: __('Please fill out all required fields', 'events')
											}
										>
											<Attendees {...{ state, dispatch }} />
										</WizardStep>
									)}
									<WizardStep
										valid={wizard.steps.registration.valid}
										invalidMessage={__(
											'Please fill out all required fields',
											'events',
										)}
									>
										<UserRegistration {...{ state, dispatch }} />
									</WizardStep>
									<WizardStep valid={wizard.steps.payment.valid}>
										<Payment {...{ state, dispatch }} />
									</WizardStep>
									<WizardStep valid={wizard.steps.success.valid}>
										<Success {...{ state, dispatch }} />
									</WizardStep>
								</Wizard>
							</div>
							<div className="event-modal-footer">
								<WizardControls state={state} dispatch={dispatch} />
							</div>
						</>
					)}
				</div>
			</div>
		</div>
	);
};

export default Booking;
