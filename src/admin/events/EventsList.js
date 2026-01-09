import { useState, useEffect } from 'react'; // Imports nicht vergessen ;)
import apiFetch from '@wordpress/api-fetch';
import { formatDateRange } from '../../shared/formatDate.js';

const EventsList = () => {
    // State
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(false); // Lade-Indikator ist UX-Gold
    const [view, setView] = useState({
        scope: 'future',
        search: '',
        page: 1,
        per_page: 2, // 2 ist bisschen wenig zum Testen ;)
        totalPages: 1,
        totalItems: 0,
        // Sortierung und Spalten hab ich mal dringelassen, falls du sie später brauchst
        sortBy: 'name', 
        sortOrder: 'asc',
    });

    // Fetch Funktion (sauber getrennt)
    const fetchEvents = async () => {
        setLoading(true); // Ladebalken an
        try {
            const response = await apiFetch({
                // Query Strings bauen ist so sauberer und lesbarer:
                path: `/events/v3/events?include=location,tags,categories&scope=${view.scope}&page=${view.page}&per_page=${view.per_page}`,
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
            setLoading(false); // Ladebalken aus, egal ob Erfolg oder Fehler
        }
    };
    
    // Effect: Nur feuern, wenn sich Scope oder Pagination ändert!
    useEffect(() => {
        const loadData = async () => {
            const { events, total, pages } = await fetchEvents();
            
            setEvents(events);
            
            // WICHTIG: Hier updaten wir view. 
            // Da wir im Dependency Array (unten) NICHT mehr [view] stehen haben,
            // sondern nur view.page etc., löst dieses Update KEINEN Loop aus.
            setView(prevView => ({
                ...prevView,
                totalPages: parseInt(pages || '1', 10), 
                totalItems: parseInt(total || '0', 10),
            }));
        };

        loadData();

    // HIER WAR DER FEHLER:
    // Wir hören nur auf Inputs (Trigger), nicht auf Outputs (Ergebnisse wie totalItems).
    }, [view.scope, view.page, view.per_page]); 

    // Helper für Pagination-Wechsel (behandelt Scope-Reset gleich mit)
    const handleScopeChange = (newScope) => {
        setView(prev => ({ ...prev, scope: newScope, page: 1 })); // Bei neuem Scope immer auf Seite 1
    };

    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= view.totalPages) {
            setView(prev => ({ ...prev, page: newPage }));
        }
    };


    return (
        <div>
            <h1>Events List</h1>
            
            {/* React nutzt className statt class! */}
            <div className="tablenav top">
                <div className="alignleft actions bulkactions">
                    <label htmlFor="bulk-action-selector-top" className="screen-reader-text">Mehrfachaktion wählen</label>

                    <select 
                        value={view.scope} 
                        onChange={(e) => handleScopeChange(e.target.value)}
                    >
                        <option value="future">Future Events</option>
                        <option value="past">Past Events</option>
                        <option value="all">All Events</option>
                    </select>
                </div>
                {/* Pagination Oben auch anzeigen? WordPress macht das meistens. */}
            </div>

            {loading && <p>Lade Daten...</p>}
            
            {!loading && events.length === 0 && <p>Keine Events gefunden.</p>}

            {!loading && events.length > 0 && (
                <div>
                    <table className="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td id="cb" className="manage-column column-cb check-column">
                                    <input id="cb-select-all-1" type="checkbox"/>
                                </td>
                                <th className="manage-column column-title column-primary">Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Tags</th>
                                <th>Categories</th>
                            </tr>
                        </thead>
                        <tbody>
                            {events.map((event) => (
                                <tr key={event.id}>
                                    <th className="cb column-cb check-column">
                                        <input type="checkbox"/>
                                    </th>
                                    <td className="title column-title has-row-actions column-primary page-title">
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
                                    </td>
                                    <td>{formatDateRange(event.startDate, event.endDate)}</td>
                                    <td>{event.includes?.location?.name}</td>
                                    <td>{event.includes?.tags?.map((tag) => tag.name).join(', ')}</td>
                                    <td>{event.includes?.categories?.map((category) => category.name).join(', ')}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    <div className="tablenav bottom">
                        <div className="tablenav-pages">
                            <span className="displaying-num">{view.totalItems} Einträge</span>
                            <span className="pagination-links">
                                {/* Erste Seite */}
                                <button 
                                    className={`tablenav-pages-navspan button ${view.page === 1 ? 'disabled' : ''}`}
                                    onClick={() => handlePageChange(1)}
                                    disabled={view.page === 1}
                                >«</button>
                                
                                {/* Vorherige Seite */}
                                <button 
                                    className={`tablenav-pages-navspan button ${view.page === 1 ? 'disabled' : ''}`}
                                    onClick={() => handlePageChange(view.page - 1)}
                                    disabled={view.page === 1}
                                >‹</button>

                                <span className="screen-reader-text">Aktuelle Seite</span>
                                <span id="table-paging" className="paging-input">
                                    <span className="tablenav-paging-text">{view.page} von <span className="total-pages">{view.totalPages}</span></span>
                                </span>

                                {/* Nächste Seite */}
                                <button 
                                    className={`next-page button ${view.page === view.totalPages ? 'disabled' : ''}`} 
                                    onClick={() => handlePageChange(view.page + 1)}
                                    disabled={view.page === view.totalPages}
                                >›</button>
                                
                                {/* Letzte Seite */}
                                <button 
                                    className={`last-page button ${view.page === view.totalPages ? 'disabled' : ''}`}
                                    onClick={() => handlePageChange(view.totalPages)}
                                    disabled={view.page === view.totalPages}
                                >»</button>
                            </span>
                        </div>
                        <br className="clear"/>
                    </div>
                </div>
            )}
        </div>
    );
};

export default EventsList;