import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useFeaturedEventData, type FeaturedEventContext } from '../shared';

type EditProps = {
	context?: FeaturedEventContext;
};

export default function Edit({ context }: EditProps) {
	const { imageUrl, imageAlt } = useFeaturedEventData(context);

	return (
		<figure {...useBlockProps({ className: 'wp-block-image size-large' })}>
			{imageUrl ? (
				<img src={imageUrl} alt={imageAlt} />
			) : (
				<div className="components-placeholder">
					{__('Event image will appear here.', 'ctx-events')}
				</div>
			)}
		</figure>
	);
}
