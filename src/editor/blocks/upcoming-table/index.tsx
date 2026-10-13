import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import './editor.scss';
import '../upcoming/editor.scss';
import '../upcoming/style-index.scss';

registerBlockType(metadata.name, {
	...metadata,
	title: __(metadata.title, 'ctx-events'),
	edit: Edit,
	save,
});
