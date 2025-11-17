/**
 * Internal dependencies
 */
import metadata from './block.json';
import Edit from './edit';
import './editor.scss';

/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n';
import icons from './icons';

const { name, title } = metadata;

const settings = {
	...metadata,
	title: __(title, 'events'),
	icon: icons.posts,
	edit: Edit,
	save: () => {
		return null;
	},
};

export { name, settings };
