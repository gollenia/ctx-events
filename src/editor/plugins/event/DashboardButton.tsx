import {
	__experimentalFullscreenModeClose as FullscreenModeClose,
	__experimentalMainDashboardButton as MainDashboardButton,
} from '@wordpress/edit-post';

const DashboardButton = () => {
	const currentType = (window as Window & { typenow?: string }).typenow;

	if (currentType !== 'ctx-event') {
		return null;
	}

	return (
		<MainDashboardButton>
			<FullscreenModeClose href="admin.php?page=ctx_events_admin_menu" />
		</MainDashboardButton>
	);
};

export default DashboardButton;
