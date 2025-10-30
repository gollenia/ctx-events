import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';

const MailSettings = ( props ) => {
	const { meta, setMeta } = props;
	const [ showMails, setShowMails ] = React.useState( false );

	return (
		<PanelBody
			title={ __( 'Mail Forms', 'events' ) }
			initialOpen={ true }
			className="events-booking-settings"
		></PanelBody>
	);
};

export default MailSettings;
