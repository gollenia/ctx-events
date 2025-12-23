import { registerPlugin } from '@wordpress/plugins';
import { __experimentalMainDashboardButton as MainDashboardButton } from '@wordpress/edit-post';
import { __experimentalFullscreenModeClose as FullscreenModeClose } from '@wordpress/edit-post';
import { external } from '@wordpress/icons';

const DashboardButton = () => (
    <MainDashboardButton>
        <FullscreenModeClose icon={ external } href="https://wordpress.org"/>
    </MainDashboardButton>
);

export default DashboardButton;
