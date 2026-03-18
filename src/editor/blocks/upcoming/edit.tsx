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

type UpcomingAttributes = {
	textAlignment?: string;
	selectedTags: number[];
	selectedCategory: number[];
	selectedLocation: number;
	altText: string;
};

type EntityOption = {
	id: number;
	name: string;
};

type LocationOption = {
	value: number;
	label: string;
};

type TagEntity = {
	id: number;
	name: string;
};

type LocationEntity = {
	id: number;
	title: {
		raw: string;
	};
};

type EditProps = {
	attributes: UpcomingAttributes;
	setAttributes: (attributes: Partial<UpcomingAttributes>) => void;
};

const EditUpcoming = (props: EditProps) => {
	const {
		attributes: { textAlignment, selectedTags, altText },
		setAttributes,
	} = props;

	const availableCategories = useSelect((selectFn) => {
		const { getEntityRecords } = selectFn(coreStore);
		const query = { hide_empty: false, per_page: -1 };
		const list = getEntityRecords(
			'taxonomy',
			'ctx-event-categories',
			query,
		) as TagEntity[] | null;

		if (!list) {
			return [];
		}

		return list.map((category) => ({ id: category.id, name: category.name }));
	}, []);

	const tagList = useSelect((selectFn) => {
		const { getEntityRecords } = selectFn(coreStore);
		const query = { hide_empty: false, per_page: -1 };
		return getEntityRecords(
			'taxonomy',
			'ctx-event-tags',
			query,
		) as TagEntity[] | null;
	}, []);

	const locationList = useSelect(() => {
		const query = { per_page: -1 };
		const list = (select(coreStore).getEntityRecords(
			'postType',
			'ctx-event-location',
			query,
		) ?? []) as LocationEntity[];

		return [
			{ value: 0, label: '' },
			...list.map((location) => ({
				value: location.id,
				label: location.title.raw,
			})),
		];
	}, []);

	const tagNames = (tagList ?? []).map((tag) => tag.name);
	const tagsFieldValue = selectedTags
		.map((tagId) => tagList?.find((tag) => tag.id === tagId)?.name ?? null)
		.filter((value): value is string => value !== null);

	const blockProps = useBlockProps();

	return (
		<>
			<Inspector
				{...props}
				tagList={tagList ?? []}
				availableCategories={availableCategories}
				tagsFieldValue={tagsFieldValue}
				tagNames={tagNames}
				locationList={locationList}
			/>
			<BlockControls>
				<AlignmentToolbar
					value={textAlignment}
					onChange={(value) => setAttributes({ textAlignment: value })}
				/>
			</BlockControls>
			<div {...blockProps}>
				<div className="components-placeholder is-large">
					<div className="components-placeholder__label">
						{__('Upcoming Events', 'ctx-events')}
					</div>
					<div className="components-placeholder__instructions">
						<RichText
							tagName="p"
							placeholder={__(
								'Set a text here to show when no events are visible',
								'events',
							)}
							value={altText}
							onChange={(value) => {
								setAttributes({ altText: value });
							}}
						/>
					</div>
				</div>
			</div>
		</>
	);
};

export default EditUpcoming;
