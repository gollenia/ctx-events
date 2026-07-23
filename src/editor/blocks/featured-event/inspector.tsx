import { InspectorControls } from '@wordpress/block-editor';
import {
	CheckboxControl,
	ComboboxControl,
	FormTokenField,
	PanelBody,
	RadioControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import type { ComboboxControlOption } from '@wordpress/components/build-types/combobox-control/types';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import EventSelector from './EventSelector';
import type {
	FeaturedEventAttributes,
	FeaturedEventContext,
} from './types';

type EditProps = {
	attributes: FeaturedEventAttributes;
	context?: FeaturedEventContext;
	setAttributes: (attributes: Partial<FeaturedEventAttributes>) => void;
};

type TermEntity = {
	id: number;
	name: string;
};

type LocationEntity = {
	id: number;
	title?: {
		raw?: string;
		rendered?: string;
	};
};

export default function Inspector({
	attributes,
	context,
	setAttributes,
}: EditProps) {
	const [categoryQuery, setCategoryQuery] = useState('');

	const categories = useSelect((selectFn) => {
		const { getEntityRecords } = selectFn(coreStore);
		return (
			(getEntityRecords('taxonomy', 'ctx-event-categories', {
				hide_empty: false,
				per_page: -1,
			}) as TermEntity[] | null) ?? []
		);
	}, []);

	const tags = useSelect((selectFn) => {
		const { getEntityRecords } = selectFn(coreStore);
		return (
			(getEntityRecords('taxonomy', 'ctx-event-tags', {
				hide_empty: false,
				per_page: -1,
			}) as TermEntity[] | null) ?? []
		);
	}, []);

	const locations = useSelect((selectFn) => {
		const { getEntityRecords } = selectFn(coreStore);
		const items =
			(getEntityRecords('postType', 'ctx-event-location', {
				per_page: -1,
			}) as LocationEntity[] | null) ?? [];

		return [
			{ value: '0', label: __('Any location', 'ctx-events') },
			...items.map(
				(location): ComboboxControlOption => ({
					value: String(location.id),
					label:
						location.title?.raw ||
						location.title?.rendered ||
						`${__('Location', 'ctx-events')} #${location.id}`,
				}),
			),
		];
	}, []);

	const filteredCategories = useMemo(() => {
		if (categoryQuery.trim().length < 2) {
			return categories;
		}

		return categories.filter((category) =>
			category.name.toLowerCase().includes(categoryQuery.trim().toLowerCase()),
		);
	}, [categories, categoryQuery]);

	const selectedTags = useMemo(
		() =>
			(attributes.queryTagIds ?? [])
				.map((tagId) => tags.find((tag) => tag.id === tagId)?.name ?? null)
				.filter((value): value is string => value !== null),
		[attributes.queryTagIds, tags],
	);

	const tagSuggestions = useMemo(() => tags.map((tag) => tag.name), [tags]);

	const scopeOptions = [
		{ value: 'future', label: __('Next upcoming event', 'ctx-events') },
		{ value: 'today', label: __('Today', 'ctx-events') },
		{ value: 'this-week', label: __('This week', 'ctx-events') },
		{ value: 'this-month', label: __('This month', 'ctx-events') },
	];

	return (
		<InspectorControls>
			<PanelBody title={__('Selection', 'ctx-events')} initialOpen>
				<RadioControl
					label={__('Source', 'ctx-events')}
					selected={attributes.selectionMode ?? 'manual'}
					options={[
						{
							label: __('Specific event', 'ctx-events'),
							value: 'manual',
						},
						{
							label: __('Next matching event', 'ctx-events'),
							value: 'query',
						},
						{
							label: __('Current event', 'ctx-events'),
							value: 'current',
						},
					]}
					onChange={(value) =>
						setAttributes({
							selectionMode:
								(value as FeaturedEventAttributes['selectionMode']) ?? 'manual',
						})
					}
				/>

				{(attributes.selectionMode ?? 'manual') === 'manual' ? (
					<EventSelector
						attributes={attributes}
						context={context}
						onChange={setAttributes}
					/>
				) : null}

				{(attributes.selectionMode ?? 'manual') === 'query' ? (
					<>
						<SelectControl
							label={__('Scope', 'ctx-events')}
							value={attributes.queryScope ?? 'future'}
							options={scopeOptions}
							onChange={(value) => setAttributes({ queryScope: value })}
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>
						<TextControl
							label={__('Filter categories', 'ctx-events')}
							value={categoryQuery}
							onChange={setCategoryQuery}
							placeholder={__('Type to narrow categories...', 'ctx-events')}
							__next40pxDefaultSize
						/>
						{filteredCategories.map((category) => (
							<CheckboxControl
								key={category.id}
								label={category.name}
								checked={(attributes.queryCategoryIds ?? []).includes(category.id)}
								onChange={(isChecked) => {
									const current = attributes.queryCategoryIds ?? [];
									setAttributes({
										queryCategoryIds: isChecked
											? [...current, category.id]
											: current.filter((id) => id !== category.id),
									});
								}}
							/>
						))}
						<FormTokenField
							label={__('Tags', 'ctx-events')}
							value={selectedTags}
							suggestions={tagSuggestions}
							onChange={(values) => {
								const nextTagIds = values
									.map((value) => tags.find((tag) => tag.name === value)?.id)
									.filter((tagId): tagId is number => tagId !== undefined);
								setAttributes({ queryTagIds: nextTagIds });
							}}
							__next40pxDefaultSize
							__experimentalExpandOnFocus
						/>
						<ComboboxControl
							label={__('Location', 'ctx-events')}
							value={String(attributes.queryLocationId ?? 0)}
							options={locations}
							onChange={(value) =>
								setAttributes({
									queryLocationId: value ? Number(value) : 0,
								})
							}
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>
					</>
				) : null}

				{(attributes.selectionMode ?? 'manual') === 'current' ? (
					<p>
						{__(
							'This mode uses the surrounding event post automatically.',
							'ctx-events',
						)}
					</p>
				) : null}
			</PanelBody>
		</InspectorControls>
	);
}
