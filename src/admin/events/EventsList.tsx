import { useState, useEffect, Fragment } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { DataTable, DataFilter, StatusSelect } from '@events/datatable';
import { formatDateRange } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import { Button, Flex } from '@wordpress/components';
import { bookingDenyReason } from './bookingDenyReason';

import { eventStatusItems } from './eventStatusItems';
import RowActions from '@events/datatable/RowActions';
import { Event } from 'src/types/types';
import { DataTableField } from '@events/datatable/DataTable';

const EventsList = () => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(false);
	const [statusItems, setStatusItems] = useState({});
    const [view, setView] = useState({
        scope: 'future',
        bookable: '',
		status: 'publish',
        search: '',
        page: 1,
        perPage: 20,
        totalPages: 1,
        totalItems: 0,	
		sortBy: 'startDate', 
        sortOrder: 'asc',
    });


    const filterConfig = [
        {
            key: 'scope',
            type: 'select',
            options: {
                'future': 'Zukünftige Events',
                'past': 'Vergangene Events',
                'all': 'Alle Events'
            }
        },
        {
            key: 'bookable',
            type: 'select',
            label: 'Buchungen',
            options: {
                '1': 'Ist buchbar',
                '0': 'Nicht buchbar'
            }
        },
		{
            key: 'status',
            type: 'select',
            label: 'Status',
            options: {
				'publish': 'Veröffentlicht',
                'future': 'Geplant',
				'draft': 'Entwurf',
				'trash': 'Abgesagt',
            }
        }
    ];

    const fields: Array<DataTableField> = [
        {
            label: 'Name',
			id: 'title',
            className: 'column-title column-primary has-row-actions',
            enableSorting: true,
            render: (event: Event) => (
				<><strong><a href={`/wp-admin/post.php?post=${event.id}&action=edit`}>{event.name || __('(No title)', 'ctx-events')}</a></strong>
					<RowActions
						actions={[
							{
								label: __('Duplicate', 'ctx-events'),
								slug: 'duplicate',
								callback: async (slug) => {
									try {
										const response: { success: boolean; data: { id: number } } = await apiFetch({
											path: `/events/v3/events/${event.id}/duplicate`,
											method: 'POST'
										});
										if (response.success) {
											window.location.href = `/wp-admin/post.php?post=${response.data.id}&action=edit`;
										}
									} catch (error) {
										console.error(error);
									}
								}
							},
							{
								label: __('Cancel', 'ctx-events'),
								delete: true,
								slug: 'bookings',
								callback: (slug) => {
									window.location.href = `/wp-admin/edit.php?post_type=ctx-booking&event_id=${event.id}`;
								}
							},
							
						]}
					/>
						

				</>
            )
        },
        {
            label: __('Date', 'ctx-events'),
			id: 'date',
            enableSorting: true,
			getValue: (event: Event) => formatDateRange(event.startDate, event.endDate ?? false)
        },
        {
            label: 'Location',
			id: 'location',
            render: (event: Event) => {
				if (event.includes?.location) {
					return <><strong><a href={`/wp-admin/post.php?post=${event.includes.location.id}&action=edit`}>{event.includes.location.name || __('(No Location)', 'ctx-events')}</a></strong><br/>
					{event.includes.location.address && <span>{event.includes.location.address.addressCountry}</span>}
					</>;
				}
				return <>—</>
			},
			enableSorting: true,
        },
        {
            label: 'Tags',
			id: 'tags',
            getValue: (event: Event) => event.includes?.tags?.map((t: { name: string }) => t.name).join(', '),
			enableSorting: true,
        },
        {
			id: 'categories',
            label: 'Categories',
            getValue: (event: Event) => event.includes?.categories?.map((c: { name: string }) => c.name).join(', '),
			enableSorting: true,
        },
		{
			id: 'price',
			label: 'Preis',
			getValue: (event: Event) => {
				if (!event.bookingSummary) {
					return '—';
				}
				console.log('Rendering price for event:', event.name, event.bookingSummary);
				if (event.bookingSummary.lowestPrice && event.bookingSummary.highestPrice) {
					if (event.bookingSummary.lowestPrice.amountCents === event.bookingSummary.highestPrice.amountCents) {
						return `${(event.bookingSummary.lowestPrice.amountCents / 100).toFixed(2)} ${event.bookingSummary.lowestPrice.currency}`;
					}
					return `${(event.bookingSummary.lowestPrice.amountCents / 100).toFixed(2)} - ${(event.bookingSummary.highestPrice.amountCents / 100).toFixed(2)} ${event.bookingSummary.lowestPrice.currency}`;
				}


				return __('N/A', 'ctx-events');
			},
			enableSorting: true,
		},
		{
			id: 'bookable',
			label: 'Buchbar',
			render: (event: Event) => {
				if(!event.bookingSummary) {
					return <>—</>;
				}
				if(!event.bookingSummary.isBookable) {
					return <span className='danger'>{bookingDenyReason(event.bookingSummary)}</span>;
				}
				return <span className='trashed'>{event.bookingSummary.isBookable ? 'Ja' : 'Nein'}</span>;
			},
			enableSorting: true,
		}
    ];

    const fetchEvents = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                include: 'location,tags,categories,bookings',
                page: view.page.toString(),
                per_page: view.perPage.toString(),
				orderby: view.sortBy,   
                order: view.sortOrder,
            });

            if (view.scope) params.append('scope', view.scope);
            if (view.bookable && view.bookable !== 'all') params.append('bookable', view.bookable); 
            if (view.status && view.status !== 'all') params.append('status', view.status);
            if (view.search) params.append('search', view.search);
			console.log('Fetching events with params:', params.toString());
            const response = await apiFetch({
                path: `/events/v3/events?${params.toString()}`, 
                parse: false
            });

            const total = response.headers.get('X-WP-Total');
            const pages = response.headers.get('X-WP-TotalPages');
			const statusItems = response.headers.get('X-WP-StatusCounts') ? JSON.parse(response.headers.get('X-WP-StatusCounts') ?? '{}') : {};
            const data = await response.json();

            return { events: data, total, pages, statusItems };
        } catch (error) {
            console.error(error);
            return { events: [], total: 0, pages: 0, statusItems: {} };
        } finally {
            setLoading(false);
        }
    };

	const handleSort = (columnKey: string) => {
        setView(prev => {
    
            if (prev.sortBy === columnKey) {
                return {
                    ...prev,
                    sortOrder: prev.sortOrder === 'asc' ? 'desc' : 'asc'
                };
            }
    
            return {
                ...prev,
                sortBy: columnKey,
                sortOrder: 'asc'
            };
        });
    };

    const handleFilterChange = (key: string, value: any) => {
        setView(prev => ({
            ...prev,
            [key]: value, 
            page: 1
        }));
    };

	console.log('Status counts from headers:', statusItems);
    const handlePageChange = (newPage: number) => { /* ... */ };

    useEffect(() => {
        const loadData = async () => {
            const { events, total, pages, statusItems } = await fetchEvents();
            setEvents(events);
            setView(prev => ({
                ...prev,
                totalPages: parseInt(String(pages || '1'), 10),
                totalItems: parseInt(String(total || '0'), 10),
            }));
			setStatusItems(statusItems);
			
        };
        loadData();
    }, [view.scope, view.bookable, view.status,view.page, view.perPage, view.search, view.sortBy, view.sortOrder]); 

    return (
        <div className="wrap">
            <div>
				<h1 className="wp-heading-inline">{__('Events', 'ctx-events')}</h1>
				<a href="/wp-admin/post-new.php?post_type=ctx-event" className="page-title-action">{__('New Event', 'ctx-events')}</a>
			</div>

			<StatusSelect
				statusItems={eventStatusItems(statusItems)}
				currentStatus={view.status}
				onChange={(value: string) => handleFilterChange('status', value)}
			/>
            
            <DataFilter 
                filters={filterConfig} 
                view={view} 
                onFilterChange={handleFilterChange} 
            />

            <DataTable 
                items={events}
                fields={fields}
                view={view}
				onSort={handleSort}
                onPageChange={handlePageChange}
                loading={loading}
				noItemsMessage={__('No events found.', 'ctx-events')}
            />
        </div>
    );
};

export default EventsList;