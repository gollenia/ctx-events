import domReady from '@wordpress/dom-ready';

import './booking/index.tsx';
import './booking/style.scss';
import initUpcoming from './upcoming/index.tsx';

domReady(() => {
	initUpcoming();
});
