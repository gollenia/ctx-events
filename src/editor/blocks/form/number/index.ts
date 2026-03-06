import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';
import './editor.scss';
import icons from './icons';

const { name, title, description } = metadata;

const settings = {
	...metadata,
	title: __(title, 'ctx-events'),
	description: __(description, 'ctx-events'),
	icon: icons.icon,
	edit: Edit,
	save: () => {
		return null;
	},
};

export { name, settings };
