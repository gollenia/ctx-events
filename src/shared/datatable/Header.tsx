import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useDataTable } from './DataTableContext';

interface HeaderProps {
	title?: string;
	createLink?: string;
	createLinkLabel?: string;
}

const Header = ({ title, createLink, createLinkLabel }: HeaderProps) => {
	return (
		<>
			<h1 className="wp-heading-inline">
				{title || __('Items', 'ctx-events')}
			</h1>
			{createLink && (
				<a href={createLink} className="page-title-action">
					{createLinkLabel || __('New Item', 'ctx-events')}
				</a>
			)}
			<hr className="wp-header-end" />
		</>
	);
};

const DataTableHeader = () => {
	const { title, createLink, createLinkLabel } = useDataTable();
	return (
		<Header
			title={title}
			createLink={createLink}
			createLinkLabel={createLinkLabel}
		/>
	);
};

export default Header;
export { DataTableHeader };
