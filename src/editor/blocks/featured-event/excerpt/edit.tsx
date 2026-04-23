import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useFeaturedEventData, type FeaturedEventContext } from '../shared';

type EditProps = {
	context?: FeaturedEventContext;
};

export default function Edit({ context }: EditProps) {
	const { excerpt } = useFeaturedEventData(context);

	return (
		<p {...useBlockProps()}>
			{excerpt || __('Event excerpt will appear here.', 'ctx-events')}
		</p>
	);
}
