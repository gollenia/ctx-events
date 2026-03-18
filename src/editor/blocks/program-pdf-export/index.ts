import metadata from './block.json';
import Edit from './edit';
import './editor.scss';
import icon from './icon';
import './style.scss';

const { name } = metadata;

const settings = {
	...metadata,
	icon,
	edit: Edit,
	save: () => null,
};

export { name, settings };
