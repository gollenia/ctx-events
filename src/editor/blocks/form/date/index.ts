/**
 * Internal dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import './editor.scss';
import icon from './icon';

const { name } = metadata;

const settings = {
	...metadata,
	icon,
	edit: Edit,
	save: () => {
		return null;
	},
};

registerBlockType(name, settings);

export { name, settings };
