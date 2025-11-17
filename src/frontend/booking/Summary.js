import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Gateway from './gateway';
import { formatCurrency } from './modules/priceUtils';

function Summary({ state, dispatch }) {
	const { event, response, request, wizard } = state;

	const [TICKETS, REGISTRATION, PAYMENT, SUCCESS] = [
		wizard.step == 0,
		wizard.step == 1,
		wizard.step == 2,
		wizard.step == 3,
	];

	const ticketCount = request.attendees.length;

	const ticketPrice = (key) => {
		return (
			event.tickets_available[key].price *
			request.attendees.reduce((n, attendee) => {
				return n + (attendee.ticket_id == event.tickets_available[key].id);
			}, 0)
		);
	};

	const countTicketsById = (id) => {
		const count = request.attendees.reduce((n, attendee) => {
			return n + (attendee.ticket_id == id);
		}, 0);
		return count;
	};

	const calculateFullPrice = () => {
		let sum = 0;
		for (const ticket in event.tickets_available) {
			sum += ticketPrice(ticket);
		}

		if (!response.coupon.success) return sum + request.donation;
		sum = response.coupon.percent
			? sum - (sum / 100) * parseInt(response.coupon.discount)
			: sum - parseInt(response.coupon.discount);
		return sum + request.donation;
	};

	const TICKETS_MISSING =
		(TICKETS && request.attendees.length == 0) ||
		(REGISTRATION &&
			event.forms.attendee_fields.length == 0 &&
			request.attendees.length == 0);

	const fullPrice = useMemo(
		() => calculateFullPrice(),
		[response.coupon, ticketCount, request.donation],
	);

	return (
		<>
			<div className="list ticket-summary">
				{Object.keys(event.tickets_available).map((id, key) => {
					const maxSpaces = Math.min(
						event.available_spaces - request.attendees.length,
						event.tickets_available[id].max - countTicketsById(id),
					);

					return (
						<div className="list__item" key={key}>
							<div className="list__content">
								<div className="list__title">
									{event.tickets_available[id].name}
								</div>
								<div className="list__subtitle">
									{event.tickets_available[id].description}
								</div>
								<div className="list__subtitle">
									{__('Base price:', 'events')}{' '}
									{formatCurrency(
										event.tickets_available[id].price,
										window.eventBookingLocalization.locale,
										window.eventBookingLocalization.currency,
									)}
								</div>
								{maxSpaces < 5 && (
									<div className="list__subtitle has-red-text">
										{__('Available:', 'events')} {maxSpaces}
									</div>
								)}
							</div>

							<div className="list__actions">
								<span className="button button--pseudo nowrap">
									{formatCurrency(
										ticketPrice(id),
										window.eventBookingLocalization.locale,
										window.eventBookingLocalization.currency,
									)}
								</span>
								{event.forms.attendee_fields.length == 0 && (
									<div className="number-picker">
										<button
											className="button button--primary button--icon"
											onClick={() =>
												dispatch({ type: 'REMOVE_ATTENDEE', payload: { id } })
											}
											disabled={
												event.tickets_available[id].min == countTicketsById(id)
											}
										></button>
										<input
											value={countTicketsById(event.tickets_available[id].id)}
										/>
										<button
											className="button button--primary button--icon"
											onClick={() =>
												dispatch({ type: 'ADD_ATTENDEE', payload: id })
											}
											disabled={
												event.tickets_available[id].max == countTicketsById(id)
											}
										></button>
									</div>
								)}
								{event.forms.attendee_fields.length > 0 && wizard.step == 0 && (
									<>
										<button
											className={`button button--primary button--icon ${
												TICKETS_MISSING ? 'button--breathing' : ''
											}`}
											onClick={() =>
												dispatch({ type: 'ADD_ATTENDEE', payload: id })
											}
											disabled={
												event.tickets_available[id].max ==
													countTicketsById(id) ||
												request.attendees.length == event.available_spaces
											}
										>
											<i className="material-icons material-symbols-outlined">
												add_circle
											</i>
										</button>
									</>
								)}
							</div>
						</div>
					);
				})}
				{response.coupon.success && (
					<div className="list__item">
						<div className="list__content">
							<div className="list__title">
								{response.coupon.description || __('Coupon', 'events')}
							</div>
						</div>
						<div className="list__actions">
							<b className="button button--pseudo nowrap">
								{response.coupon.percent
									? response.coupon.discount + ' %'
									: formatCurrency(
											response.coupon.discount,
											window.eventBookingLocalization.locale,
											window.eventBookingLocalization.currency,
										)}
							</b>
							{event.forms.attendee_fields.length == 0 && (
								<div className="number-picker invisible">
									<button className="button button--primary button--icon"></button>
									<input />
									<button className="button button--primary button--icon"></button>
								</div>
							)}
						</div>
					</div>
				)}
				<div className="list__item">
					<div className="list__content">
						<div className="list__title">
							<b>{__('Full price', 'events')}</b>
						</div>
					</div>
					<div className="list__actions">
						<b className="button button--pseudo nowrap">
							{formatCurrency(
								fullPrice,
								window.eventBookingLocalization.locale,
								window.eventBookingLocalization.currency,
							)}
						</b>
						{event.forms.attendee_fields.length == 0 && (
							<div className="number-picker invisible">
								<button className="button button--primary button--icon"></button>
								<input />
								<button className="button button--primary button--icon"></button>
							</div>
						)}
						{wizard.step == 0 && (
							<button className="button button--primary button--icon invisible">
								<i className="material-icons material-symbols-outlined">
									add_circle
								</i>
							</button>
						)}
					</div>
				</div>
			</div>
			{wizard.step == 2 && (
				<div>
					<Gateway state={state} />
				</div>
			)}
		</>
	);
}

export default Summary;
