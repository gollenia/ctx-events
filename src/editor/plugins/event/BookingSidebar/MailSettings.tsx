import { Notice, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import type { BookingSidebarProps } from './types';

const MailSettings = (_props: BookingSidebarProps) => {
	return (
		<PanelBody
			title={__('Mail Settings', 'ctx-events')}
			initialOpen={true}
			className="events-booking-settings"
		>
			<Notice status="info" isDismissible={false}>
				{__(
					'Email templates are not configured in this sidebar yet. The previous editor UI here was incomplete and has been removed until it is backed by a real save flow.',
					'ctx-events',
				)}
			</Notice>
		</PanelBody>
	);
};

export default MailSettings;
