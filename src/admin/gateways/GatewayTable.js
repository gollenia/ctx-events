import { DataTable, RowActions } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { check, closeSmall, Icon, trash } from '@wordpress/icons';
import classNames from 'classnames';
import GatewayModal from './GatewayModal';

const GatewayTable = () => {
	const [gateways, setGateways] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [activeSlug, setActiveSlug] = useState('');

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
			label: __('Title', 'events'),
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
								label: __('Edit', 'events'),
								callback: () => setActiveSlug(gateway.slug),
							},
							{
								label: gateway.active
									? __('Disable', 'events')
									: __('Enable', 'events'),
								delete: true,
								callback: () => onToggle(gateway.slug, gateway.active),
							},
						]}
					/>
				</>
			),
			sortable: true,
			className: 'column-title column-primary has-row-actions',
		},
		{
			label: __('Description', 'events'),
			id: 'adminName',
			sortale: true,
		},
		{
			label: __('Active', 'events'),
			id: 'active',
			sortable: true,
			render: (item) => {
				return item.active ? <Icon icon={check} /> : <Icon icon={closeSmall} />;
			},

			enableSorting: false,
		},
	];

	const onSort = () => {};

	useEffect(() => {
		apiFetch({ path: '/events/v3/gateways' })
			.then((data) => {
				setGateways(data);
				setLoading(false);
			})
			.catch((err) => {
				setError(err.message);
				setLoading(false);
			});
	}, []);

	const onToggle = (slug, isActive) => {
		apiFetch({
			path: `/events/v3/gateways/${slug}`,
			method: 'PATCH',
			data: { enabled: !isActive },
		})
			.then((data) => {
				console.log(data);
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
			<DataTable
				items={gateways}
				columns={columns}
				view={view}
				onSort={onSort}
				loading={loading}
				noItemsMessage={__('No gateways found.', 'ctx-events')}
			/>

			<GatewayModal slug={activeSlug} onCancel={onCancel} />
		</div>
	);
};

export default GatewayTable;
