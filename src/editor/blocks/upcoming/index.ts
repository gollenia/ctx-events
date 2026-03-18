import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import deprecated from './deprecated';
import Edit from './edit';
import './editor.scss';
import icons from './icons';
import save from './save';
import './style.scss';

const { name, title } = metadata;

const settings = {
	...metadata,
	title: __(title, 'ctx-events'),
	icon: icons.posts,
	edit: Edit,
	save,
	deprecated,
};

export { name, settings };
