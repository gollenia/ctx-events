import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useFeaturedEventData, type FeaturedEventContext } from '../shared';

type EditProps = {
	attributes: {
		level: number;
	};
	context?: FeaturedEventContext;
};

export default function Edit({ attributes, context }: EditProps) {
	const { title } = useFeaturedEventData(context);
	const level = Math.min(6, Math.max(1, Number(attributes.level || 2)));
	const tagName = `h${level}` as const;

	return (
		<RichText
			{...useBlockProps({ className: 'wp-block-heading' })}
			tagName={tagName}
			value={title}
			withoutInteractiveFormatting
			allowedFormats={[]}
			readOnly
		/>
	);
}
