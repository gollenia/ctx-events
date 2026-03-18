import domReady from '@wordpress/dom-ready';
import initUpcoming from './upcoming/index.tsx';
import './booking/index.tsx';
import './booking/style.scss';

domReady(() => {
	initUpcoming();
});
