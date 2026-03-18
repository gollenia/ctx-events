import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import Inspector from './inspector';

type DetailsAttributes = {
	dividers: boolean;
};

type EditProps = {
	attributes: DetailsAttributes;
	className?: string;
	setAttributes: (attributes: Partial<DetailsAttributes>) => void;
};

export default function Edit(props: EditProps) {
	const {
		attributes: { dividers },
		className,
	} = props;

	const allowedBlocks = [
		'ctx-events/details-audience',
		'ctx-events/details-time',
		'ctx-events/details-person',
		'ctx-events/details-spaces',
		'ctx-events/details-shutdown',
		'ctx-events/details-date',
		'ctx-events/details-item',
		'ctx-events/details-price',
		'ctx-events/details-location',
	];

	const template = [
		['ctx-events/details-audience'],
		['ctx-events/details-date'],
		['ctx-events/details-time'],
		['ctx-events/details-person'],
		['ctx-events/details-location'],
		['ctx-events/details-price'],
		['ctx-events/details-spaces'],
		['ctx-events/details-shutdown'],
	] as const;

	const classes = ['event-details', className, dividers ? 'has-divider' : false]
		.filter(Boolean)
		.join(' ');

	const blockProps = useBlockProps({ className: classes });
	const innerBlocksProps = useInnerBlocksProps(
		{},
		{ allowedBlocks, template, templateLock: false },
	);

	return (
		<div {...blockProps}>
			<Inspector {...props} />
			<div {...innerBlocksProps} />
		</div>
	);
}
