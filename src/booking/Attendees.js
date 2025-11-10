import { useRef } from 'react';
import Attendee from './Attendee';
import Summary from './Summary';

const Attendees = ( props ) => {
	const { state, dispatch } = props;

	const { request, event } = state;

	const form = useRef( null );

	return (
		<div className="ticket-grid">
			<Summary state={ state } dispatch={ dispatch } />
			{ event.forms.attendee_fields.length > 0 && (
				<form className="ticket-grid-form" role="form" ref={ form } id="user-attendee-form">
					<div id="firstTicket"></div>
					{ request.attendees.map( ( attendee, index ) => (
						<Attendee
							key={ index }
							attendee={ attendee }
							index={ index }
							state={ state }
							dispatch={ dispatch }
						/>
					) ) }
				</form>
			) }
		</div>
	);
};

export default Attendees;
