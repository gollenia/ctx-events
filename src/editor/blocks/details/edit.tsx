import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import Inspector from './inspector';

type DetailsAttributes = {
	dividers: boolean;
};

type EditProps = {
	attributes: DetailsAttributes;
	className?: string;
	context?: {
		postType?: string;
	};
	setAttributes: (attributes: Partial<DetailsAttributes>) => void;
};

export default function Edit(props: EditProps) {
	const {
		attributes: { dividers },
		className,
		context,
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

	const classes = ['event-details', className, dividers ? 'has-dividers' : false]
		.filter(Boolean)
		.join(' ');

	const blockProps = useBlockProps({ className: classes });
	const innerBlocksProps = useInnerBlocksProps(
		{},
		{ allowedBlocks, template, templateLock: false },
	);
	const isInvalidContext = context?.postType !== 'ctx-event';

	return (
		<div {...blockProps}>
			<Inspector {...props} />
			{isInvalidContext ? (
				<Placeholder
					label={__('Event Details', 'ctx-events')}
					instructions={__(
						'This block only works inside event posts and can be removed here.',
						'ctx-events',
					)}
				/>
			) : (
				<div {...innerBlocksProps} />
			)}
		</div>
	);
}
