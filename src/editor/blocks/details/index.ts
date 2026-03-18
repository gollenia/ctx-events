import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';
import './editor.scss';
import icon from './icon';
import save from './save';
import './style.scss';

const { name, title, description } = metadata;

const settings = {
	...metadata,
	title: __(title, 'ctx-events'),
	description: __(description, 'ctx-events'),
	icon,
	edit: Edit,
	save,
};

export { name, settings };
