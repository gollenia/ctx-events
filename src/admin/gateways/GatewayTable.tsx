import {
	type DataFieldConfig,
	DataTable,
	type DataTableAction,
	type DataViewConfig,
} from '@events/datatable';
import { DataTableFilter } from '@events/datatable/Filter';
import { mapStatusItems } from '@events/datatable/statusItems';
import apiFetch from '@wordpress/api-fetch';
import { SnackbarList } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';
import type { Gateway } from 'src/types/types';
import StatusPill, { type StatusPillState } from '../../shared/status-pill';
import GatewayModal from './GatewayModal';

const gatewayStatusConfig: Record<
	Gateway['status'],
	{ label: string; state: StatusPillState }
> = {
	missing_configuration: {
		label: __('Missing configuration', 'ctx-events'),
		state: 'error',
	},
	inactive: {
		label: __('Inactive', 'ctx-events'),
		state: 'info',
	},
	active: {
		label: __('Active', 'ctx-events'),
		state: 'success',
	},
	test_mode: {
		label: __('Test mode', 'ctx-events'),
		state: 'info',
	},
};

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
		fields: ['title', 'adminName', 'status'],
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
			label: __('Status', 'ctx-events'),
			id: 'status',
			enableSorting: true,
			render: (gateway) => {
				const status = gatewayStatusConfig[gateway.status] ?? {
					label: gateway.status,
					state: 'info' as const,
				};

				return <StatusPill state={status.state}>{status.label}</StatusPill>;
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
		apiFetch<{ active: boolean; status: Gateway['status'] }>({
			path: `/events/v3/gateways/${slug}`,
			method: 'PATCH',
			data: { enabled: !isActive },
		})
			.then(({ active, status }) => {
				setGateways((previous) => previous.map((gateway) =>
					gateway.slug === slug ? { ...gateway, enabled: active, status } : gateway,
				));
			})
			.catch((err) => {
				const message = err?.error || err?.message ||
					__('An error occurred while toggling the gateway.', 'ctx-events');
				createErrorNotice(message, {
					id: slug,
					type: 'snackbar',
					explicitDismiss: false,
				});
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
				<DataTable.Table />
				<DataTable.Pagination />
			</DataTable>
			<SnackbarList notices={notices.filter((notice) => notice.type === 'snackbar')} onRemove={removeNotice} />

			{activeSlug && <GatewayModal key={activeSlug} slug={activeSlug} onCancel={onCancel} />}
		</div>
	);
};

export default GatewayTable;
