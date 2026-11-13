import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Countdown from './Countdown';
import type { CountdownAttributes } from './types';

type Term = { id: number; name: string };
type Location = { id: number; title?: { raw?: string; rendered?: string } };
type Props = { attributes: CountdownAttributes; setAttributes: (value: Partial<CountdownAttributes>) => void };

function Edit({ attributes, setAttributes }: Props) {
	const { categories, tags, locations } = useSelect((select) => {
		const { getEntityRecords } = select(coreStore);
		return {
			categories: (getEntityRecords('taxonomy', 'ctx-event-categories', { per_page: -1, hide_empty: false }) ?? []) as Term[],
			tags: (getEntityRecords('taxonomy', 'ctx-event-tags', { per_page: -1, hide_empty: false }) ?? []) as Term[],
			locations: (getEntityRecords('postType', 'ctx-event-location', { per_page: -1 }) ?? []) as Location[],
		};
	}, []);
	const blockProps = useBlockProps();
	return <>
		<InspectorControls>
			<PanelBody title={__('Display', 'ctx-events')} initialOpen>
				<SelectControl label={__('View', 'ctx-events')} value={attributes.displayMode} options={[
					{ label: __('Countdown', 'ctx-events'), value: 'countdown' },
					{ label: __('Date only', 'ctx-events'), value: 'date' },
				]} onChange={(value) => setAttributes({ displayMode: value as CountdownAttributes['displayMode'] })} />
			</PanelBody>
			<PanelBody title={__('Selection', 'ctx-events')} initialOpen>
				<SelectControl label={__('Category', 'ctx-events')} value={String(attributes.categoryId)} options={[
					{ label: __('All', 'ctx-events'), value: '0' }, ...categories.map((item) => ({ label: item.name, value: String(item.id) })),
				]} onChange={(value) => setAttributes({ categoryId: Number(value) })} />
				<SelectControl label={__('Tags', 'ctx-events')} value={String(attributes.tagId)} options={[
					{ label: __('All', 'ctx-events'), value: '0' }, ...tags.map((item) => ({ label: item.name, value: String(item.id) })),
				]} onChange={(value) => setAttributes({ tagId: Number(value) })} />
				<SelectControl label={__('Location', 'ctx-events')} value={String(attributes.locationId)} options={[
					{ label: __('Any location', 'ctx-events'), value: '0' }, ...locations.map((item) => ({ label: item.title?.raw || item.title?.rendered || `#${item.id}`, value: String(item.id) })),
				]} onChange={(value) => setAttributes({ locationId: Number(value) })} />
			</PanelBody>
		</InspectorControls>
		<div {...blockProps}><Countdown attributes={attributes} /></div>
	</>;
}

function Save({ attributes }: { attributes: CountdownAttributes }) {
	return <div {...useBlockProps.save({ className: 'ctx-events-countdown' })} data-attributes={JSON.stringify(attributes)} />;
}

registerBlockType(metadata.name, {
	...metadata,
	supports: { ...metadata.supports, align: ['wide', 'full'] },
	title: __(metadata.title, 'ctx-events'),
	description: __(metadata.description, 'ctx-events'),
	edit: Edit,
	save: Save,
});
