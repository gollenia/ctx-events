import { Panel, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import AdminField from '../../shared/adminfields/AdminField';

const Registration = ({ store }) => {
	const [state, dispatch] = store;
	const data = state.data;

	return (
		<div className="booking-registration">
			<h2>{__('Registration', 'events')}</h2>

			<Panel>
				<PanelBody header="Registration">
					{data.registration_fields.map((field) => (
						<AdminField
							{...field}
							key={field.fieldid}
							label={field.label}
							value={data.booking.registration[field.fieldid]}
							onChange={(value) =>
								dispatch({
									type: 'SET_FIELD',
									payload: {
										form: 'registration',
										field: field.fieldid,
										value,
									},
								})
							}
						/>
					))}
				</PanelBody>
			</Panel>
		</div>
	);
};

export default Registration;
