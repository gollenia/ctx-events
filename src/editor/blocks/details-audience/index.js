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
import icon from './icon';

const { name, title, description } = metadata;

const settings = {
	...metadata,
	title: __(title, 'events'),
	description: __(description, 'events'),
	icon,
	edit: Edit,
	save: () => {
		return null;
	},
};

export { name, settings };
