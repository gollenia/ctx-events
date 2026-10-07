import {
	__experimentalFullscreenModeClose as FullscreenModeClose,
	__experimentalMainDashboardButton as MainDashboardButton,
} from '@wordpress/edit-post';

import REWRITES from './dashboardRewrites';

const DashboardButton = () => {
	const currentType = (window as Window & { typenow?: string }).typenow;
	console.log(currentType);
	if (!(currentType && REWRITES[currentType])) {
		return null;
	}

	return (
		<MainDashboardButton>
			<FullscreenModeClose
				href={`admin.php?page=${REWRITES[currentType] ?? ''}`}
			/>
		</MainDashboardButton>
	);
};

export default DashboardButton;
