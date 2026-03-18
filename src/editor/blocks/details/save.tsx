import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

type DetailsStyleAttributes = {
	spacing?: {
		blockGap?: string;
	};
};

type SaveProps = {
	attributes: {
		dividers: boolean;
		style?: DetailsStyleAttributes;
	};
};

export default function Save({ attributes }: SaveProps) {
	const { dividers, style } = attributes;
	const gapStyle = !style?.spacing?.blockGap
		? {}
		: {
				gap:
					style.spacing.blockGap.replaceAll('|', '--').replace(':', '(--wp--') +
					')',
			};

	const className = ['event-details', dividers ? 'has-dividers' : false]
		.filter(Boolean)
		.join(' ');

	const blockProps = useBlockProps.save({ className, style: gapStyle });
	const innerBlocksProps = useInnerBlocksProps.save(blockProps);

	return <div {...innerBlocksProps} />;
}
