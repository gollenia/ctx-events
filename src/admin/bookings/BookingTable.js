import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	__experimentalHStack as HStack,
	Icon,
	__experimentalText as Text,
} from '@wordpress/components';
import { DataViews, filterSortAndPaginate } from '@wordpress/dataviews/wp';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	drafts,
	notAllowed,
	pending,
	published,
	swatch,
	trash,
} from '@wordpress/icons';
import { formatPrice } from '../../shared/formatPrice';
import { initialViewFromURL } from './filters';

const BookingTable = () => {
	const [bookings, setBookings] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [slug, setSlug] = useState('');

	const [view, setView] = useState(
		initialViewFromURL({
			type: 'table',
			search: '',
			page: 1,
			perPage: 100,
			fields: ['user_email', 'event', 'price', 'date', 'status', 'gateway'],
			filters: [],
			layout: {},
			titleField: 'full_name',
			sort: {
				order: 'asc',
				orderby: 'date',
			},
		}),
	);

	console.log(bookings);

	const StatusIcon = (status) => {
		console.log(status);
		switch (status.status) {
			case 0:
				return <Icon icon={drafts} />;
			case 1:
				return <Icon icon={published} />;
			case 2:
				return <Icon icon={notAllowed} />;
			case 3:
				return <Icon icon={swatch} />;
			case 6:
				return <Icon icon={pending} />;
		}
		return <Icon icon={trash} />;
	};

	const fields = [
		{
			label: __('Name', 'events'),
			id: 'full_name',
			enableHiding: false,
			enableGlobalSearch: true,
			elements: [],
			type: 'string',
		},
		{
			label: __('E-Mail', 'events'),
			id: 'user_email',
			enableHiding: false,
			enableGlobalSearch: true,
			editable: true,
			type: 'string',
		},
		{
			label: __('Event', 'events'),
			id: 'event',
			enableSorting: false,
			filterBy: {
				operators: ['is'],
			},
			enableGlobalSearch: true,
			elements: [],
			type: 'string',
			render: ({ item }) => {
				return <a href={`#event_id=${item.event.id}`}>{item.event?.title}</a>;
			},
		},
		{
			label: __('Price', 'events'),
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
			label: __('Donation', 'events'),
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
			label: __('Attendees', 'events'),
			id: 'attendees',
			type: 'string',
			render: ({ item }) => {
				return item.tickets.map((ticket) => (
					<div key={ticket.id}>
						{ticket.title} x {ticket.quantity}
					</div>
				));
			},
			enableSorting: false,
		},
		{
			label: __('ID', 'events'),
			id: 'id',
			type: 'number',
			enableSorting: true,
		},
		{
			label: __('Gateway', 'events'),
			id: 'gateway',
			type: 'string',
			enableSorting: true,
		},
		{
			label: __('Date', 'events'),
			id: 'date',
			filterBy: {
				operators: ['is', 'before', 'after'],
			},
			type: 'date',
			format: 'Y-m-d H:i',
			enableSorting: true,
		},

		{
			label: __('Status', 'events'),
			id: 'status',
			type: 'string',
			filterBy: {
				operators: ['is', 'is not'],
			},
			elements: [
				{ value: 0, label: __('Pending', 'events') },
				{ value: 1, label: __('Approved', 'events') },
				{ value: 2, label: __('Rejected', 'events') },
				{ value: 3, label: __('Canceled', 'events') },
				{ value: 6, label: __('Awaiting Payment', 'events') },
				{ value: 7, label: __('Payment Paid', 'events') },
			],
			render: ({ item }) => {
				return (
					<HStack style={{ width: 'auto' }}>
						<StatusIcon status={item.status} />
						<Text>{item.status_array[item.status] || item.status}</Text>
					</HStack>
				);
			},
			enableSorting: true,
		},
	];

	const { data: shownData, paginationInfo } = useMemo(() => {
		return filterSortAndPaginate(bookings, view, fields);
	}, [view, bookings]);

	useEffect(() => {
		apiFetch({ path: '/events/v2/bookings' })
			.then((data) => {
				setBookings(data);
				setLoading(false);
			})
			.catch((err) => {
				setError(err.message);
				setLoading(false);
			});
	}, [view]);

	const onToggle = (slug) => {
		apiFetch({
			path: `/events/v2/gateway/toggle`,
			method: 'POST',
			data: { slug },
		})
			.then((data) => {
				console.log(data);
				setGateways((prev) => {
					const updatedGateways = prev.map((gateway) => {
						if (gateway.slug === slug) {
							return {
								...gateway,
								active: !gateway.active,
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
		setSlug('');
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
				<h1>{__('Bookings', 'events')}</h1>
				<Button variant="secondary" onClick={() => setShowAddNew(true)}>
					{__('Export', 'events')}
				</Button>
			</HStack>
			{/* { slug && <GatewayModal slug={ slug } onClose={ onCancel } onToggle={ onToggle } /> } */}
			<DataViews
				data={shownData}
				view={view}
				onChangeView={setView}
				paginationInfo={paginationInfo}
				// You can define custom columns if needed
				defaultLayouts={{
					table: {
						// Define default table layout settings
						spacing: 'normal',
						showHeader: true,
					},
				}}
				fields={fields}
				actions={[
					{
						id: 'approve',
						label: __('Approve', 'events'),
						icon: trash,
						callback: async ([item]) => {
							setStatus(item.id, 'approved');
						},
					},
					{
						id: 'reject',
						label: __('Reject', 'events'),
						icon: trash,
						callback: async ([item]) => {
							setStatus(item.id, 'rejected');
						},
					},
					{
						id: 'delete',
						label: __('Delete', 'events'),
						icon: <Icon icon={trash} />,
						callback: async ([item]) => {
							setSlug(item.slug);
						},
					},
				]}
			/>
		</div>
	);
};

export default BookingTable;
