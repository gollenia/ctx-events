import { sanitizeHtml } from '../sanitize';

export type HTMLBlockProps = {
	content?: string;
	width?: number;
	role?: 'note' | 'region' | 'presentation';
};

const HTMLBlock = ({
	content = '',
	role,
}: HTMLBlockProps) => {
	const classes = ['ctx-content-block', 'core-block'].join(' ');

	return (
		<div
			className={classes}
			role={role}
			dangerouslySetInnerHTML={{
				__html: sanitizeHtml(content),
			}}
		/>
	);
};

export default HTMLBlock;
