import { InputField } from '@contexis/wp-react-form';
//import InputField from '../__experimantalForm/InputField';

/*
 *	Renders a single ticket with it's form fields
 *  and a delete button
 *
 */
const Attendee = ( props ) => {
	const { state, dispatch, attendee, index } = props;

	const { attendee_fields } = state.event.forms;
	const ticket = state.event.tickets_available[ attendee.ticket_id ];

	return (
		<div className="booking-ticket">
			<div className="booking-ticket-title">
				<h4>{ ticket?.name }</h4>
				<button
					href=""
					className="button button--danger button--pop"
					onClick={ () => dispatch( { type: 'REMOVE_TICKET', payload: { index } } ) }
					disabled={ ticket?.min >= index + 1 }
				>
					<i className="material-icons material-symbols-outlined">delete</i>
				</button>
			</div>
			<div className="booking-ticket-form">
				{ attendee_fields.map( ( field, key ) => {
					return (
						<InputField
							{ ...field }
							name={ field.fieldid }
							key={ key }
							id={ ( ( index == key ) == state.wizard.step ) == 0 ? 'first-ticket-field' : '' }
							tabIndex={ `${ index + 1 }${ key }` }
							value={ attendee.fields[ field.fieldid ] ?? '' }
							onChange={ ( value ) => {
								dispatch( {
									type: 'SET_FIELD',
									payload: { form: 'attendee', index, field: field.fieldid, value: value },
								} );
							} }
							locale={ window.eventBookingLocalization?.locale }
						/>
					);
				} ) }
			</div>
		</div>
	);
};

export default Attendee;
