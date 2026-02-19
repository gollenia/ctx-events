/**
 * Internal dependencies
 */
import metadata from './block.json';
import Edit from './edit';
import './editor.scss';

/**
 * Wordpress dependencies
 */
import { withColors } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import icon from './icon';

const { name, title, description } = metadata;

const settings = {
	...metadata,
	title: __(title, 'ctx-events'),
	description: __(description, 'ctx-events'),
	icon,
	edit: withColors({ buttonColor: 'buttonColor' })(Edit),
	save: () => {
		return null;
	},
};

export { name, settings };
