import { PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import MailModal from './MailModal';

const MailSettings = (props) => {
	const { meta, setMeta } = props;
	const [showMails, setShowMails] = useState(false);

	return (
		<PanelBody
			title={__('Mail Forms', 'ctx-events')}
			initialOpen={true}
			className="events-booking-settings"
		>

				<p>{__('Configure the email forms that will be sent to users when they book an event.', 'ctx-events')}</p>



				<button style={{ marginTop: '16px' }} onClick={() => setShowMails(true)}>
					{__('Open Mail Settings', 'ctx-events')}
				</button>

				
		</PanelBody>
	);
};

export default MailSettings;
