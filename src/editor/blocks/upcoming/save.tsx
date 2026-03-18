import { useBlockProps } from '@wordpress/block-editor';

type SaveProps = {
	attributes: Record<string, unknown>;
};

function SaveUpcoming(props: SaveProps) {
	const blockProps = useBlockProps.save({ className: 'events-upcoming-block' });
	const jsonAttributes = JSON.stringify(props.attributes);

	return (
		<div
			{...blockProps}
			className="events-upcoming-block"
			data-attributes={jsonAttributes}
		/>
	);
}

export default SaveUpcoming;
