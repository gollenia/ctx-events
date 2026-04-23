import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useFeaturedEventData, type FeaturedEventContext } from '../shared';

type EditProps = {
	context?: FeaturedEventContext;
};

export default function Edit({ context }: EditProps) {
	const { locationName } = useFeaturedEventData(context);

	return (
		<p {...useBlockProps()}>
			{locationName || __('Event location will appear here.', 'ctx-events')}
		</p>
	);
}
