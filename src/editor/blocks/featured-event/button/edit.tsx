import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useFeaturedEventData, type FeaturedEventContext } from '../shared';

type EditProps = {
	attributes: {
		text: string;
	};
	context?: FeaturedEventContext;
	setAttributes: (attributes: { text?: string }) => void;
};

export default function Edit({ attributes, context, setAttributes }: EditProps) {
	const { link } = useFeaturedEventData(context);

	return (
		<div {...useBlockProps({ className: 'wp-block-button' })}>
			<RichText
				tagName="a"
				className="wp-block-button__link wp-element-button"
				value={attributes.text || __('View event', 'ctx-events')}
				placeholder={__('View event', 'ctx-events')}
				onChange={(text) => setAttributes({ text })}
				withoutInteractiveFormatting
				allowedFormats={[]}
				href={link || '#'}
			/>
		</div>
	);
}
