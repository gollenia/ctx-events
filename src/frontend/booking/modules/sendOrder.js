import apiFetch from '@wordpress/api-fetch';
import { STATES } from './constants';

const sendOrder = (state, dispatch) => {
	const controller = new AbortController();
	const signal = controller.signal;

	const { request, event, response, modal } = state;
	dispatch({ type: 'SET_ORDER_STATE', payload: STATES.LOADING });

	setTimeout(() => {
		if (modal.orderState == STATES.IDLE) return;
		dispatch({ type: 'SET_ORDER_STATE', payload: STATES.DELAY });
	}, 3000);

	setTimeout(() => {
		if (modal.orderState == STATES.IDLE) return;
		dispatch({ type: 'SET_ORDER_STATE', payload: STATES.HUGE_DELAY });
	}, 7000);

	setTimeout(() => {
		if (modal.orderState == STATES.IDLE) return;
		dispatch({ type: 'SET_ORDER_STATE', payload: STATES.ERROR });
		controller.abort();
	}, 10000);

	const fetchRequest = {
		...request,
		_wpnonce: event._nonce,
		event_id: event.id,
	};

	console.log('Fetch Request', fetchRequest);

	apiFetch({
		path: `/events/v2/booking/`,
		method: 'POST',
		data: fetchRequest,
		signal: signal,
	})
		.then((response) => {
			console.log(response);
			dispatch({
				type: 'BOOKING_RESPONSE',
				payload: { state: STATES.SUCCESS, response },
			});
			if (response.gateway.url) {
				window.location.replace(response.gateway.url);
			}
		})
		.catch((error) => {
			console.log(error);
			dispatch({
				type: 'BOOKING_RESPONSE',
				payload: {
					state: STATES.ERROR,
					response: { result: false, message: error },
				},
			});
		});
};

export default sendOrder;
