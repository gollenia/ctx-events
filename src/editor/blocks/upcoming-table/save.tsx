import { useBlockProps } from '@wordpress/block-editor';

type SaveProps = { attributes: Record<string, unknown> };

export default function SaveUpcomingTable({ attributes }: SaveProps) {
	const blockProps = useBlockProps.save({ className: 'events-upcoming-table-block' });

	return (
		<div
			{...blockProps}
			className="events-upcoming-table-block"
			data-attributes={JSON.stringify(attributes)}
		/>
	);
}
