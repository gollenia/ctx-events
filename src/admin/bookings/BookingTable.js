import { DataTable } from '@events/datatable';
import { formatPrice } from '@events/i18n';
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	__experimentalHStack as HStack,
	Icon,
	__experimentalText as Text,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	drafts,
	notAllowed,
	pending,
	published,
	swatch,
	trash,
} from '@wordpress/icons';
import { initialViewFromURL } from './filters';

const BookingTable = () => {
	const [bookings, setBookings] = useState([]);
	const [total, setTotal] = useState(0);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);

	const [view, setView] = useState(
		initialViewFromURL({
			type: 'table',
			search: '',
			page: 1,
			perPage: 25,
			fields: ['email', 'event', 'price', 'date', 'status', 'gateway'],
			filters: [],
			layout: {},
			titleField: 'email',
			sort: {
				order: 'desc',
				orderby: 'date',
			},
		}),
	);

	const StatusIcon = ({ status }) => {
		switch (status) {
			case 1:
				return <Icon icon={pending} />;
			case 2:
				return <Icon icon={published} />;
			case 3:
				return <Icon icon={notAllowed} />;
			case 4:
				return <Icon icon={swatch} />;
			default:
				return <Icon icon={drafts} />;
		}
	};

	const statusLabels = {
		1: __('Pending', 'ctx-events'),
		2: __('Approved', 'ctx-events'),
		3: __('Canceled', 'ctx-events'),
		4: __('Expired', 'ctx-events'),
		9: __('Deleted', 'ctx-events'),
	};

	const fields = [
		{
			label: __('Name', 'ctx-events'),
			id: 'name',
			enableHiding: false,
			enableGlobalSearch: false,
			type: 'string',
			render: ({ item }) => `${item.name.first} ${item.name.last}`.trim(),
		},
		{
			label: __('E-Mail', 'ctx-events'),
			id: 'email',
			enableHiding: false,
			enableGlobalSearch: false,
			type: 'string',
		},
		{
			label: __('Event', 'ctx-events'),
			id: 'event',
			enableSorting: false,
			filterBy: {
				operators: ['is'],
			},
			enableGlobalSearch: false,
			elements: [],
			type: 'string',
			render: ({ item }) => {
				return <a href={`#event_id=${item.event.id}`}>{item.event?.title}</a>;
			},
		},
		{
			label: __('Price', 'ctx-events'),
			id: 'price',
			type: 'string',
			render: ({ item }) => {
				return formatPrice(
					item.price,
					window.eventBlocksLocalization?.currency || 'USD',
				);
			},
			enableSorting: false,
		},
		{
			label: __('Donation', 'ctx-events'),
			id: 'donation',
			type: 'string',
			render: ({ item }) => {
				return formatPrice(
					item.donation,
					window.eventBlocksLocalization?.currency || 'USD',
				);
			},
			enableSorting: false,
		},
		{
			label: __('Spaces', 'ctx-events'),
			id: 'spaces',
			type: 'string',
			render: ({ item }) => item.spaces,
			enableSorting: false,
		},
		{
			label: __('ID', 'ctx-events'),
			id: 'id',
			type: 'number',
			enableSorting: true,
		},
		{
			label: __('Gateway', 'ctx-events'),
			id: 'gateway',
			type: 'string',
			enableSorting: true,
		},
		{
			label: __('Date', 'ctx-events'),
			id: 'date',
			filterBy: {
				operators: ['is', 'before', 'after'],
			},
			type: 'date',
			format: 'Y-m-d H:i',
			enableSorting: true,
		},
		{
			label: __('Status', 'ctx-events'),
			id: 'status',
			type: 'integer',
			filterBy: {
				operators: ['is', 'is not'],
			},
			elements: [
				{ value: 1, label: __('Pending', 'ctx-events') },
				{ value: 2, label: __('Approved', 'ctx-events') },
				{ value: 3, label: __('Canceled', 'ctx-events') },
				{ value: 4, label: __('Expired', 'ctx-events') },
			],
			render: ({ item }) => {
				return (
					<HStack style={{ width: 'auto' }}>
						<StatusIcon status={item.status} />
						<Text>{statusLabels[item.status] || item.status}</Text>
					</HStack>
				);
			},
			enableSorting: true,
		},
	];

	useEffect(() => {
		const params = new URLSearchParams();
		params.set('page', String(view.page ?? 1));
		params.set('per_page', String(view.perPage ?? 25));

		if (view.search) {
			params.set('search', view.search);
		}

		if (view.sort?.orderby) {
			params.set('order_by', view.sort.orderby);
		}

		if (view.sort?.order) {
			params.set('order', view.sort.order);
		}

		const eventIdFilter = view.filters?.find((f) => f.field === 'event_id');
		if (eventIdFilter?.value) {
			params.set('event_id', String(eventIdFilter.value));
		}

		const statusFilter = view.filters?.find((f) => f.field === 'status');
		if (statusFilter?.value) {
			const values = Array.isArray(statusFilter.value)
				? statusFilter.value
				: [statusFilter.value];
			values.forEach((value) => params.append('status[]', String(value)));
		}

		setLoading(true);
		apiFetch({ path: `/events/v3/bookings?${params.toString()}`, parse: false })
			.then(async (response) => {
				const totalHeader = response.headers.get('X-WP-Total');
				if (totalHeader !== null) {
					setTotal(parseInt(totalHeader, 10));
				}
				const data = await response.json();
				setBookings(data);
				setLoading(false);
			})
			.catch((err) => {
				setError(err.message);
				setLoading(false);
			});
	}, [view]);

	const paginationInfo = {
		totalItems: total,
		totalPages: Math.ceil(total / (view.perPage ?? 25)),
	};

	if (loading) {
		return <div>Loading...</div>;
	}

	if (error) {
		return <div>Error: {error}</div>;
	}

	return (
		<div>
			<HStack
				style={{ marginBottom: '1em', padding: '12px 48px', width: 'auto' }}
			>
				<h1>{__('Bookings', 'ctx-events')}</h1>
				<Button variant="secondary">
					{__('Export', 'ctx-events')}
				</Button>
			</HStack>
			<DataTable
				data={bookings}
				view={view}
				onChangeView={setView}
				paginationInfo={paginationInfo}
				defaultLayouts={{
					table: {
						spacing: 'normal',
						showHeader: true,
					},
				}}
				fields={fields}
				actions={[
					{
						id: 'approve',
						label: __('Approve', 'ctx-events'),
						icon: published,
						callback: async ([item]) => {
							await apiFetch({
								path: `/events/v3/bookings/${item.reference}`,
								method: 'PATCH',
								data: { status: 'approved' },
							});
						},
					},
					{
						id: 'cancel',
						label: __('Cancel', 'ctx-events'),
						icon: notAllowed,
						callback: async ([item]) => {
							await apiFetch({
								path: `/events/v3/bookings/${item.reference}`,
								method: 'PATCH',
								data: { status: 'canceled' },
							});
						},
					},
					{
						id: 'delete',
						label: __('Delete', 'ctx-events'),
						icon: trash,
						callback: async ([item]) => {
							await apiFetch({
								path: `/events/v3/bookings/${item.reference}`,
								method: 'DELETE',
							});
						},
					},
				]}
			/>
		</div>
	);
};

export default BookingTable;
