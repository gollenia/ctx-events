import domReady from '@wordpress/dom-ready';
import initUpcoming from './upcoming/index.js';
import './booking/index.tsx';
import './booking/style.scss';

domReady(() => {
	initUpcoming();
});
