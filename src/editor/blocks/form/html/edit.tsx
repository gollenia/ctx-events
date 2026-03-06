import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import Inspector from './inspector';

type HtmlAttributes = {
	width: number;
	visibilityRule?: {
		field: string;
		value: 'checked' | 'unchecked';
	} | null;
};

interface EditProps {
	attributes: HtmlAttributes;
	setAttributes: (attributes: Partial<HtmlAttributes>) => void;
	clientId: string;
}

export default function Edit(props: EditProps) {
	const {
		attributes: { width },
	} = props;

	const template = [['core/paragraph']];
	const allowedBlocks = [
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/quote',
		'core/image',
		'core/group',
	];

	const innerBlocksProps = useInnerBlocksProps(
		{},
		{ allowedBlocks, template, templateLock: false },
	);

	const blockProps = useBlockProps({
		className: ['ctx:event-html', `ctx:event-html--${width}`]
			.filter(Boolean)
			.join(' '),
	});

	return (
		<div {...blockProps}>
			<Inspector
				attributes={props.attributes}
				setAttributes={props.setAttributes}
				clientId={props.clientId}
			/>

			<div {...innerBlocksProps}></div>
		</div>
	);
}
