import {
	__experimentalFullscreenModeClose as FullscreenModeClose,
	__experimentalMainDashboardButton as MainDashboardButton,
} from '@wordpress/edit-post';
import { external } from '@wordpress/icons';

const DashboardButton = () => {
	return (
		<MainDashboardButton>
			<FullscreenModeClose icon={external} href="https://wordpress.org" />
		</MainDashboardButton>
	);
};

export default DashboardButton;
