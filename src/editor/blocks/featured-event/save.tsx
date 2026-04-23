import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function save() {
	return (
		<div {...useBlockProps.save({ className: 'ctx-featured-event' })}>
			<InnerBlocks.Content />
		</div>
	);
}
