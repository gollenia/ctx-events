import { useState, useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch';
import { DataTable, DataFilter } from '@events/datatable';
import { formatDateRange } from '@events/i18n';
import { __ } from '@wordpress/i18n';

const EventsList = () => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(false);
    const [view, setView] = useState({
        scope: 'future',
        bookable: '',
        search: '',
        page: 1,
        per_page: 10,
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
            label: 'Buchungsstatus...',
            options: {
                'yes': 'Ist buchbar',
                'no': 'Nicht buchbar'
            }
        },
    ];

    const columns = [
        {
            label: 'Name',
            className: 'column-title column-primary has-row-actions',
            sortable: true,
            render: (event) => (
                <>
                    <strong>
                        <a href={`/wp-admin/post.php?post=${event.id}&action=edit`}>{event.name}</a>
                    </strong>
                    <div className="row-actions visible">
                        <span className="edit">
                            <a href={`/wp-admin/post.php?post=${event.id}&action=edit`}>Bearbeiten</a> | 
                        </span>
                        <span className="trash">
                            <a href={`/wp-admin/post.php?post=${event.id}&action=trash`} className="submitdelete">Absagen</a>
                        </span>
                    </div>
                </>
            )
        },
        {
            label: __('Date', 'ctx-events'),
            sortable: true,
            render: (event) => formatDateRange(event.startDate, event.endDate)
        },
        {
            label: 'Location',
            render: (event) => event.includes?.location?.name || '—',
			sortable: true,
        },
        {
            label: 'Tags',
            render: (event) => event.includes?.tags?.map(t => t.name).join(', '),
			sortable: true,
        },
        {
            label: 'Categories',
            render: (event) => event.includes?.categories?.map(c => c.name).join(', '),
			sortable: true,
        }
    ];

    const fetchEvents = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                include: 'location,tags,categories',
                page: view.page,
                per_page: view.per_page,
				orderby: view.sortBy,   
                order: view.sortOrder,
            });

            // Spezifische Filter anfügen
            if (view.scope) params.append('scope', view.scope);
            if (view.bookable) params.append('bookable', view.bookable); 
            if (view.search) params.append('search', view.search);

            const response = await apiFetch({
                path: `/events/v3/events?${params.toString()}`, 
                parse: false
            });

            const total = response.headers.get('X-WP-Total');
            const pages = response.headers.get('X-WP-TotalPages');
            const data = await response.json();

            return { events: data, total, pages };
        } catch (error) {
            console.error(error);
            return { events: [], total: 0, pages: 0 };
        } finally {
            setLoading(false);
        }
    };

	const handleSort = (columnKey) => {
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

    const handleFilterChange = (key, value) => {
        setView(prev => ({
            ...prev,
            [key]: value, 
            page: 1
        }));
    };

    const handlePageChange = (newPage) => { /* ... */ };

    useEffect(() => {
        const loadData = async () => {
            const { events, total, pages } = await fetchEvents();
            setEvents(events);
            setView(prev => ({
                ...prev,
                totalPages: parseInt(pages || '1', 10), 
                totalItems: parseInt(total || '0', 10),
            }));
        };
        loadData();
    }, [view.scope, view.bookable, view.page, view.per_page, view.search, view.sortBy, view.sortOrder]); 

    return (
        <div>
            <h1>Events List</h1>
            
            <DataFilter 
                filters={filterConfig} 
                view={view} 
                onFilterChange={handleFilterChange} 
            />

            <DataTable 
                items={events}
                columns={columns}
                view={view}
				onSort={handleSort}
                onPageChange={handlePageChange}
                loading={loading}
            />
        </div>
    );
};

export default EventsList;