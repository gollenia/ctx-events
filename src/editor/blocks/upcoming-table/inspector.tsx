import { InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	CheckboxControl,
	ComboboxControl,
	FormTokenField,
	PanelBody,
	RangeControl,
	SelectControl,
} from '@wordpress/components';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ColumnSettingsModal from './ColumnSettingsModal';

type Term = { id: number; name: string };
type Option = { value: number; label: string };
type Attributes = {
	limit?: number;
	scope?: string;
	order?: 'asc' | 'desc';
	selectedCategory: number[];
	selectedLocation: number;
	selectedTags: number[];
	includeRecurring?: boolean;
	excludeCurrent?: boolean;
	showCategory?: boolean;
	showLocation?: string;
	showAudience?: boolean;
	showPerson?: string;
	excerptLength?: number;
	showBookedUp?: boolean;
	bookedUpWarningThreshold?: number;
	showCategoryFilter?: boolean;
	showTagFilter?: boolean;
	showSearch?: boolean;
	filterPosition?: 'top' | 'side';
	tableShowHeader?: boolean;
	tableShowDividers?: boolean;
	tableAlternatingRows?: boolean;
	tableHoverEffect?: boolean;
	tableShowCalendarIcon?: boolean;
	tableColumns?: string[];
};
type Props = {
	attributes: Attributes;
	setAttributes: (attributes: Partial<Attributes>) => void;
	categories: Term[];
	tags: Term[];
	tagNames: string[];
	selectedTagNames: string[];
	locations: Option[];
};

export default function EventsTableInspector({
	attributes,
	setAttributes,
	categories,
	tags,
	tagNames,
	selectedTagNames,
	locations,
}: Props) {
	const postType = (
		select('core/editor') as { getCurrentPostType: () => string }
	).getCurrentPostType();
	const columnOptions = [
		{ value: 'date', label: __('Date', 'ctx-events') },
		{ value: 'title', label: __('Event', 'ctx-events') },
		{ value: 'category', label: __('Category', 'ctx-events') },
		{ value: 'description', label: __('Description', 'ctx-events') },
		{ value: 'audience', label: __('Audience', 'ctx-events') },
		{ value: 'person', label: __('Person', 'ctx-events') },
		{ value: 'location', label: __('Location', 'ctx-events') },
		{ value: 'booking', label: __('Booking', 'ctx-events') },
	];
	const selectedColumns = (attributes.tableColumns ?? [])
		.map(
			(value) => columnOptions.find((option) => option.value === value)?.label,
		)
		.filter((label): label is string => Boolean(label));
	const [isColumnModalOpen, setIsColumnModalOpen] = useState(false);

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen>
				{categories.map((category) => (
					<CheckboxControl
						key={category.id}
						label={category.name}
						checked={attributes.selectedCategory.includes(category.id)}
						onChange={(checked) =>
							setAttributes({
								selectedCategory: checked
									? [...attributes.selectedCategory, category.id]
									: attributes.selectedCategory.filter(
											(id) => id !== category.id,
										),
							})
						}
					/>
				))}
				<FormTokenField
					label={__('Tags', 'ctx-events')}
					value={selectedTagNames}
					suggestions={tagNames}
					onChange={(names) =>
						setAttributes({
							selectedTags: names
								.map((name) => tags.find((tag) => tag.name === name)?.id)
								.filter((id): id is number => id !== undefined),
						})
					}
					__next40pxDefaultSize
					__experimentalExpandOnFocus
				/>
				<ComboboxControl
					label={__('Location', 'ctx-events')}
					value={String(attributes.selectedLocation)}
					options={locations}
					onChange={(value) =>
						setAttributes({ selectedLocation: value ? Number(value) : 0 })
					}
					__next40pxDefaultSize
				/>
				<SelectControl
					label={__('Scope', 'ctx-events')}
					value={attributes.scope}
					options={[
						{ value: 'future', label: __('Future', 'ctx-events') },
						{ value: 'today', label: __('Today', 'ctx-events') },
						{ value: 'tomorrow', label: __('Tomorrow', 'ctx-events') },
						{ value: 'this-month', label: __('This month', 'ctx-events') },
						{ value: 'next-month', label: __('Next month', 'ctx-events') },
					]}
					onChange={(scope) => setAttributes({ scope })}
				/>
				<SelectControl
					label={__('Sorting', 'ctx-events')}
					value={attributes.order}
					options={[
						{ value: 'asc', label: __('Ascending', 'ctx-events') },
						{ value: 'desc', label: __('Descending', 'ctx-events') },
					]}
					onChange={(order) =>
						setAttributes({ order: order as 'asc' | 'desc' })
					}
				/>
				<RangeControl
					label={__('Limit', 'ctx-events')}
					min={1}
					max={100}
					value={attributes.limit}
					onChange={(limit) => setAttributes({ limit: Number(limit) || 1 })}
				/>
				<CheckboxControl
					label={__('Include recurring', 'ctx-events')}
					checked={attributes.includeRecurring}
					onChange={(includeRecurring) => setAttributes({ includeRecurring })}
				/>
				{postType === 'ctx-event' && (
					<CheckboxControl
						label={__('Exclude current event', 'ctx-events')}
						checked={attributes.excludeCurrent}
						onChange={(excludeCurrent) => setAttributes({ excludeCurrent })}
					/>
				)}
			</PanelBody>
			<PanelBody title={__('Filter', 'ctx-events')}>
				<CheckboxControl
					label={__('Show category filter', 'ctx-events')}
					checked={attributes.showCategoryFilter}
					onChange={(showCategoryFilter) =>
						setAttributes({ showCategoryFilter })
					}
				/>
				<CheckboxControl
					label={__('Show tag filter', 'ctx-events')}
					checked={attributes.showTagFilter}
					onChange={(showTagFilter) => setAttributes({ showTagFilter })}
				/>
				<CheckboxControl
					label={__('Show search bar', 'ctx-events')}
					checked={attributes.showSearch}
					onChange={(showSearch) => setAttributes({ showSearch })}
				/>
			</PanelBody>
			<PanelBody title={__('Columns', 'ctx-events')} initialOpen>
				<Button variant="secondary" onClick={() => setIsColumnModalOpen(true)}>
					{__('Configure columns', 'ctx-events')}
				</Button>
				<p>{selectedColumns.join(', ')}</p>
				<RangeControl
					label={__('Length of preview text', 'ctx-events')}
					min={0}
					max={200}
					value={attributes.excerptLength}
					onChange={(excerptLength) =>
						setAttributes({ excerptLength: Number(excerptLength) || 0 })
					}
				/>
				<RangeControl
					label={__('Warning threshold', 'ctx-events')}
					min={0}
					max={10}
					value={attributes.bookedUpWarningThreshold}
					onChange={(bookedUpWarningThreshold) =>
						setAttributes({
							bookedUpWarningThreshold: Number(bookedUpWarningThreshold) || 0,
						})
					}
				/>
			</PanelBody>
			<PanelBody title={__('Table options', 'ctx-events')} initialOpen>
				<CheckboxControl
					label={__('Show table header', 'ctx-events')}
					checked={attributes.tableShowHeader}
					onChange={(tableShowHeader) => setAttributes({ tableShowHeader })}
				/>
				<CheckboxControl
					label={__('Show row dividers', 'ctx-events')}
					checked={attributes.tableShowDividers}
					onChange={(tableShowDividers) => setAttributes({ tableShowDividers })}
				/>
				<CheckboxControl
					label={__('Use alternating row colors', 'ctx-events')}
					checked={attributes.tableAlternatingRows}
					onChange={(tableAlternatingRows) =>
						setAttributes({ tableAlternatingRows })
					}
				/>
				<CheckboxControl
					label={__('Show hover effect', 'ctx-events')}
					checked={attributes.tableHoverEffect}
					onChange={(tableHoverEffect) => setAttributes({ tableHoverEffect })}
				/>
				<CheckboxControl
					label={__('Show calendar icon', 'ctx-events')}
					checked={attributes.tableShowCalendarIcon}
					onChange={(tableShowCalendarIcon) =>
						setAttributes({ tableShowCalendarIcon })
					}
				/>
			</PanelBody>
			{isColumnModalOpen && (
				<ColumnSettingsModal
					columns={columnOptions}
					selectedColumns={attributes.tableColumns ?? []}
					onClose={() => setIsColumnModalOpen(false)}
					onApply={(tableColumns) => {
						setAttributes({ tableColumns });
						setIsColumnModalOpen(false);
					}}
				/>
			)}
		</InspectorControls>
	);
}
