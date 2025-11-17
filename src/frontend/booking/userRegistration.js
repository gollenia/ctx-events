import { InputField } from '@contexis/wp-react-form';
import Summary from './Summary';

const UserRegistration = (props) => {
	const { countTickets, state, dispatch } = props;

	const { error } = state.response;
	const { registration } = state.request;

	const { event, request, response } = state;

	if (!event || !event.forms.registration_fields) return null;

	return (
		<div className="grid xl:grid--columns-2 grid--gap-12">
			<Summary {...props} />
			<div>
				<form
					className="form--trap form grid xl:grid--columns-6 grid--gap-8"
					id="user-registration-form"
				>
					{event.forms.registration_fields.map((field, index) => (
						<InputField
							{...field}
							name={field.fieldid}
							disabled={state.state == 'SUBMITTING'}
							key={index}
							tabIndex={index + 1}
							type={field.type}
							value={state.request.registration[field.fieldid]}
							onChange={(event) => {
								dispatch({
									type: 'SET_FIELD',
									payload: {
										form: 'registration',
										field: field.fieldid,
										value: event,
									},
								});
							}}
							locale={window.eventBookingLocalization.locale}
						/>
					))}
					{event?.is_free && window.eventBookingLocalization.consent && (
						<InputField
							type="checkbox"
							onChange={(event) => {
								dispatch({
									type: 'SET_FIELD',
									payload: {
										form: 'registration',
										field: 'data_privacy_consent',
										value: event,
									},
								});
							}}
							tabIndex={98}
							value={request.registration.data_privacy_consent}
							settings={{
								name: 'data_privacy_consent',
								help: window.eventBookingLocalization.consent,
								type: 'checkbox',
							}}
						/>
					)}
					<div
						tabIndex={99}
						onFocus={() => {
							document.getElementById('focusButton').focus();
						}}
					></div>
					{event.is_free && error != '' && (
						<div
							class="alert bg-error text-white"
							dangerouslySetInnerHTML={{ __html: error }}
						></div>
					)}
				</form>
			</div>
		</div>
	);
};

export default UserRegistration;
