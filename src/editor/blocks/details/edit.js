/**
 * Internal dependencies
 */

/**
 * WordPress dependencies
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import Inspector from './inspector';

export default function Edit({ ...props }) {
	const allowedBlocks = [
		'ctx-events/details-audience',
		'ctx-events/details-time',
		'ctx-events/details-speaker',
		'ctx-events/details-spaces',
		'ctx-events/details-shutdown',
		'ctx-events/details-date',
		'ctx-events/details-item',
		'ctx-events/details-price',
		'ctx-events/details-audience',
		'ctx-events/details-location',
	];

	const {
		attributes: { dividers },
		className,
	} = props;

	const classes = ['event-details', className, dividers ? 'has-divider' : false]
		.filter(Boolean)
		.join(' ');

	const template = [
		['ctx-events/details-audience'],
		['ctx-events/details-date'],
		['ctx-events/details-time'],
		['ctx-events/details-speaker'],
		['ctx-events/details-location'],
		['ctx-events/details-price'],
		['ctx-events/details-spaces'],
		['ctx-events/details-shutdown'],
	];

	const blockProps = useBlockProps({ className: classes });

	const innerBlocksProps = useInnerBlocksProps(
		{},
		{ allowedBlocks, template, templateLock: false },
	);

	return (
		<div {...blockProps}>
			<Inspector {...props} />
			<div {...innerBlocksProps}></div>
		</div>
	);
}
