import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import Upcoming from '../upcoming/view/Upcoming';
import '../upcoming/view/style.scss';
import type { UpcomingAttributes } from '../upcoming/view/types';

type TableAttributes = Omit<UpcomingAttributes, 'view' | 'userStylePicker'>;

function UpcomingTable({ attributes }: { attributes: TableAttributes }) {
	return (
		<Upcoming attributes={{ ...attributes, view: 'mini', userStylePicker: false }} />
	);
}

domReady(() => {
	Array.from(document.getElementsByClassName('events-upcoming-table-block')).forEach(
		(element) => {
			const rawAttributes = element.getAttribute('data-attributes');
			if (!rawAttributes) return;
			createRoot(element).render(
				<UpcomingTable attributes={JSON.parse(rawAttributes) as TableAttributes} />,
			);
		},
	);
});
