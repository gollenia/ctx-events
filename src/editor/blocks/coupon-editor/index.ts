import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';
import './editor.scss';
import icons from './icons';

const { name, title } = metadata;

const settings = {
	...metadata,
	title: __(title, 'ctx-events'),
	icon: icons.posts,
	edit: Edit,
	save: () => null,
};

registerBlockType(name, settings);

export { name, settings };
