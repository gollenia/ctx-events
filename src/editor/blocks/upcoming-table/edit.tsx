import {
	AlignmentToolbar,
	BlockControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { select, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import Inspector from './inspector';

type Term = { id: number; name: string };
type Location = { id: number; title: { raw: string } };
type Attributes = {
	textAlignment?: string;
	selectedTags: number[];
	selectedCategory: number[];
	selectedLocation: number;
	altText: string;
};
type Props = {
	attributes: Attributes;
	setAttributes: (attributes: Partial<Attributes>) => void;
};

export default function EditEventsTable({ attributes, setAttributes }: Props) {
	const categories = useSelect((selectFn) => {
		const records = selectFn(coreStore).getEntityRecords(
			'taxonomy',
			'ctx-event-categories',
			{ hide_empty: false, per_page: -1 },
		) as Term[] | null;
		return records ?? [];
	}, []);
	const tags = useSelect((selectFn) => {
		return (
			(selectFn(coreStore).getEntityRecords('taxonomy', 'ctx-event-tags', {
				hide_empty: false,
				per_page: -1,
			}) as Term[] | null) ?? []
		);
	}, []);
	const locations = useSelect(() => {
		const records = (select(coreStore).getEntityRecords(
			'postType',
			'ctx-event-location',
			{ per_page: -1 },
		) ?? []) as Location[];
		return [
			{ value: 0, label: '' },
			...records.map(({ id, title }) => ({
				value: id,
				label: title.raw,
			})),
		];
	}, []);
	const tagNames = tags.map((tag) => tag.name);
	const selectedTagNames = attributes.selectedTags
		.map((id) => tags.find((tag) => tag.id === id)?.name)
		.filter((name): name is string => Boolean(name));

	return (
		<>
			<Inspector
				attributes={attributes}
				setAttributes={setAttributes}
				categories={categories}
				tags={tags}
				tagNames={tagNames}
				selectedTagNames={selectedTagNames}
				locations={locations}
			/>
			<BlockControls>
				<AlignmentToolbar
					value={attributes.textAlignment}
					onChange={(textAlignment) => setAttributes({ textAlignment })}
				/>
			</BlockControls>
			<div {...useBlockProps()}>
				<div className="components-placeholder is-large">
					<div className="components-placeholder__label">
						{__('Events Table', 'ctx-events')}
					</div>
					<RichText
						tagName="p"
						placeholder={__(
							'Set a text here to show when no events are visible',
							'ctx-events',
						)}
						value={attributes.altText}
						onChange={(altText) => setAttributes({ altText })}
					/>
				</div>
			</div>
		</>
	);
}
