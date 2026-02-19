import { DataTable, RowActions } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { check, cancelCircleFilled, Icon, trash } from '@wordpress/icons';
import classNames from 'classnames';
import GatewayModal from './GatewayModal';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { NoticeList } from '@wordpress/components';
import { SnackbarList } from '@wordpress/components';

const GatewayTable = () => {
	const [gateways, setGateways] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [activeSlug, setActiveSlug] = useState('');
	const { createErrorNotice } = useDispatch( noticesStore );
	const notices = useSelect( ( select ) => 
        select( noticesStore ).getNotices(), [] 
    );
    const { removeNotice } = useDispatch( noticesStore );
	const [view, setView] = useState({
		type: 'table',
		search: '',
		page: 1,
		perPage: 100,
		fields: ['description', 'active'],
		layout: {},
		filters: [],
		titleField: 'title',
		sort: {
			order: 'asc',
			orderby: 'title',
		},
	});

	const columns = [
		{
			label: __('Title', 'ctx-events'),
			render: (gateway) => (
				<>
					<strong>
						<a onClick={() => setActiveSlug(gateway.slug)} href="#">
							{gateway.title}
						</a>
					</strong>
					<RowActions
						actions={[
							{
								label: gateway.active
									? __('Deactivate', 'ctx-events')
									: __('Activate', 'ctx-events'),
								delete: true,
								disabled: !gateway.isValid,
								callback: () => onToggle(gateway.slug, gateway.active),
							},
							{
								label: __('Edit', 'ctx-events'),
								callback: () => setActiveSlug(gateway.slug),
							}
						]}
					/>
				</>
			),
			sortable: true,
			className: 'column-title column-primary has-row-actions',
		},
		{
			label: __('Description', 'ctx-events'),
			id: 'adminName',
			sortale: true,
		},
		{
			label: __('Configured', 'ctx-events'),
			id: 'isValid',
			sortable: true,
			render: (item) => {
				return item.isValid ? <Icon icon={check} /> : <Icon icon={cancelCircleFilled} />;
			},

			enableSorting: false,
		},
	];

	const onSort = () => {};

	useEffect(() => {
		apiFetch({ path: '/events/v3/gateways' })
			.then((data) => {
				console.log('Fetched gateways:', data);
				setGateways(data);
				setLoading(false);
			})
			.catch((err) => {
				setError(err.message);
				setLoading(false);
			});
	}, [activeSlug]);

	const onToggle = (slug, isActive) => {
		apiFetch({
			path: `/events/v3/gateways/${slug}`,
			method: 'PATCH',
			data: { enabled: !isActive },
		})
			.then((data) => {
				setGateways((prev) => {
					const updatedGateways = prev.map((gateway) => {
						if (gateway.slug === slug) {
							return {
								...gateway,
								active: !isActive,
							};
						}
						return gateway;
					});
					return updatedGateways;
				});
				setLoading(false);
			})
			.catch((err) => {
				setError(err.message);
				console.error(err);
				const message = __('An error occurred while toggling the gateway: ', 'ctx-events') + err.error;
				createErrorNotice( message, {
					type: 'snackbar',
					explicitDismiss: false,
				} );
				setLoading(false);
			});
	};

	const onCancel = () => {
		setActiveSlug('');
	};

	if (error) {
		return <div>Error: {error}</div>;
	}
	return (
		<div className="wrap">
			<h1 class="wp-heading-inline">{__('Payment Gateways', 'ctx-events')}</h1>

			<NoticeList
                notices={ notices }
            />

			<DataTable
				items={gateways}
				columns={columns}
				view={view}
				variant="plugins"
				onSort={onSort}
				loading={loading}
				noItemsMessage={__('No gateways found.', 'ctx-events')}
			/>
			
			<GatewayModal slug={activeSlug} onCancel={onCancel} />
		</div>
	);
};

export default GatewayTable;
