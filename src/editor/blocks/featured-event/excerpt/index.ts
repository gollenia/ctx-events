import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';

const { name, title, description } = metadata;

registerBlockType(name, {
	...metadata,
	title: __(title, 'ctx-events'),
	description: __(description, 'ctx-events'),
	edit: Edit,
	save: () => null,
});
