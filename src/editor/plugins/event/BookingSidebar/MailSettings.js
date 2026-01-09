import { PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const MailSettings = (props) => {
	const { meta, setMeta } = props;
	const [showMails, setShowMails] = useState(false);

	return (
		<PanelBody
			title={__('Mail Forms', 'events')}
			initialOpen={true}
			className="events-booking-settings"
		></PanelBody>
	);
};

export default MailSettings;
