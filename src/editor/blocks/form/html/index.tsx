import { InnerBlocks } from '@wordpress/block-editor';
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
		return <InnerBlocks.Content />;
	},
};

export { name, settings };
