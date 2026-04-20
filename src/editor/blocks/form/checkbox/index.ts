import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import './editor.scss';
import icons from './icons';

const { name } = metadata;

const settings = {
	...metadata,
	icon: icons.checkbox,
	edit: Edit,
	save: () => {
		return null;
	},
};

registerBlockType(name, settings);

export { name, settings };
