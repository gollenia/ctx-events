import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import EventsTable from './view/EventsTable';
import type { EventsTableAttributes } from './view/types';
import './style-index.scss';

domReady(() => {
	Array.from(
		document.getElementsByClassName('events-upcoming-table-block'),
	).forEach((element) => {
		const rawAttributes = element.getAttribute('data-attributes');
		if (!rawAttributes) return;
		createRoot(element).render(
			<EventsTable
				attributes={JSON.parse(rawAttributes) as EventsTableAttributes}
			/>,
		);
	});
});
