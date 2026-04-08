import {
	type DataFieldConfig,
	DataTable,
	type DataTableAction,
	type DataViewConfig,
} from '@events/datatable';
import { DataTableFilter } from '@events/datatable/Filter';
import { mapStatusItems } from '@events/datatable/statusItems';
import apiFetch from '@wordpress/api-fetch';
import { Flex, NoticeList, SnackbarList } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { cancelCircleFilled, check, Icon, trash } from '@wordpress/icons';
import { store as noticesStore } from '@wordpress/notices';
import type { Gateway } from 'src/types/types';
import GatewayModal from './GatewayModal';

const GatewayTable = () => {
	const [gateways, setGateways] = useState<Gateway[]>([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [activeSlug, setActiveSlug] = useState('');
	const { createErrorNotice } = useDispatch(noticesStore);
	const notices = useSelect((select) => select(noticesStore).getNotices(), []);
	const { removeNotice } = useDispatch(noticesStore);
	const [view, setView] = useState<DataViewConfig>({
		type: 'table',
		search: '',
		page: 1,
		perPage: 100,
		fields: ['title', 'adminName', 'isValid'],
		layout: {},
		filters: [],
		titleField: 'title',
		sort: {
			field: 'title',
			direction: 'asc',
		},
	});

	const STATUSES = [
		{ value: 'all', label: __('All', 'ctx-events') },
		{ value: 'active', label: __('Active', 'ctx-events') },
		{ value: 'inactive', label: __('Inactive', 'ctx-events') },
	];

	const gatewayStatusItems = useMemo(() => {
		return mapStatusItems(STATUSES, {
			all: gateways.length,
			active: gateways.filter((gateway) => gateway.enabled).length,
			inactive: gateways.filter((gateway) => !gateway.enabled).length,
		});
	}, [gateways]);

	console.log('Gateways:', gatewayStatusItems);

	const actions: Array<DataTableAction> = [
		{
			id: 'toggle',
			label: (gateway) => {
				return gateway.enabled
					? __('Deactivate', 'ctx-events')
					: __('Activate', 'ctx-events');
			},
			delete: true,

			callback: (items) => {
				const gateway = items[0];
				onToggle(gateway.slug, gateway.enabled);
				removeNotice(gateway.slug);
			},
		},
		{
			id: 'edit',
			label: __('Edit', 'ctx-events'),
			callback: (items) => setActiveSlug(items[0].slug),
		},
	];

	const fields: Array<DataFieldConfig> = [
		{
			id: 'title',
			label: __('Title', 'ctx-events'),
			render: (gateway) => {
				return (
					<strong>
						<a onClick={() => setActiveSlug(gateway.slug)} href="#">
							{gateway.title}
						</a>
					</strong>
				);
			},
			enableSorting: true,
			className: 'column-title column-primary has-row-actions',
		},
		{
			label: __('Description', 'ctx-events'),
			id: 'adminName',
			enableSorting: true,
		},
		{
			label: __('Configured', 'ctx-events'),
			id: 'isValid',
			enableSorting: true,
				render: (item) => {
					return item.isValid ? (
						<Flex justify="left" gap={2}>
							<Icon icon={check} />
							{item.enabled ? (
								<span className="is-configured">
									{__('Configured and active', 'ctx-events')}
								</span>
							) : (
							<span className="is-configured">
								{__('Configured for activation', 'ctx-events')}
							</span>
						)}
					</Flex>
				) : (
					<Flex justify="left" gap={2}>
						<Icon icon={cancelCircleFilled} />{' '}
						<span className="is-configured">
							{__('Not Configured for activation', 'ctx-events')}
						</span>
					</Flex>
				);
			},
		},
		{
			label: __('Status', 'ctx-events'),
			id: 'enabled',
			isVisible: false,
			isPluginStatus: true,
		},
	];

	const onSort = () => {};

	useEffect(() => {
		apiFetch({ path: '/events/v3/gateways' })
			.then((data: Gateway[]) => {
				setGateways(data);
				setLoading(false);
			})
			.catch((err) => {
				setError(err.message);
				setLoading(false);
			});
	}, [activeSlug]);

	const onToggle = (slug: string, isActive: boolean) => {
		apiFetch({
			path: `/events/v3/gateways/${slug}`,
			method: 'PATCH',
			data: { enabled: !isActive },
		})
			.then((data: Gateway) => {
				console.log(data);
				setGateways((prev) => {
						const updatedGateways = prev.map((gateway) => {
							if (gateway.slug === slug) {
								return {
									...gateway,
									enabled: !isActive,
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
				const message =
					__('An error occurred while toggling the gateway: ', 'ctx-events') +
					err.error;
				createErrorNotice(message, {
					type: 'snackbar',
					explicitDismiss: false,
				});
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
		<div>
			<DataTable<Gateway>
				data={gateways}
				title={__('Payment Gateways', 'ctx-events')}
				fields={fields}
				view={view}
				variant="plugins"
				availableStatusItems={gatewayStatusItems}
				actions={actions}
				onChangeView={(updates) => setView((prev) => ({ ...prev, ...updates }))}
				isLoading={loading}
				empty={() => <div>{__('No gateways found.', 'ctx-events')}</div>}
			>
				<DataTable.Header />
				<DataTable.StatusSelect />
				<NoticeList notices={notices} />
				<DataTable.Table />
				<DataTable.Pagination />
			</DataTable>

			<GatewayModal slug={activeSlug} onCancel={onCancel} />
		</div>
	);
};

export default GatewayTable;
