import { InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	CheckboxControl,
	FormTokenField,
	Icon,
	PanelBody,
	PanelRow,
	RadioControl,
	RangeControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useMemo, useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import icons from './icons';

type UpcomingAttributes = {
	limit: number;
	columnsSmall?: number;
	columnsMedium?: number;
	columnsLarge?: number;
	showImages: boolean;
	view: 'mini' | 'list' | 'cards';
	scope: string;
	showCategory: boolean;
	showLocation: string;
	excerptLength: number;
	selectedCategory: number[];
	selectedLocation: number;
	order: 'asc' | 'desc';
	userStylePicker: boolean;
	showAudience: boolean;
	showPerson?: string;
	showTagFilter: boolean;
	showCategoryFilter: boolean;
	showSearch: boolean;
	filterPosition: 'top' | 'side';
	showBookedUp: boolean;
	bookedUpWarningThreshold: number;
	excludeCurrent: boolean;
	selectedTags: number[];
};

type TermEntity = {
	id: number;
	name: string;
};

type LocationOption = {
	value: number;
	label: string;
};

type InspectorProps = {
	attributes: UpcomingAttributes;
	tagList: TermEntity[];
	availableCategories: TermEntity[];
	tagsFieldValue: string[];
	locationList: LocationOption[];
	tagNames: string[];
	setAttributes: (attributes: Partial<UpcomingAttributes>) => void;
};

const Inspector = (props: InspectorProps) => {
	const {
		attributes: {
			limit,
			showImages,
			view,
			scope,
			showCategory,
			showLocation,
			excerptLength,
			selectedCategory,
			selectedLocation,
			order,
			userStylePicker,
			showAudience,
			showPerson,
			showTagFilter,
			showCategoryFilter,
			showSearch,
			filterPosition,
			showBookedUp,
			bookedUpWarningThreshold,
			excludeCurrent,
		},
		tagList,
		availableCategories,
		tagsFieldValue,
		locationList,
		tagNames,
		setAttributes,
	} = props;

	const [categoryQuery, setCategoryQuery] = useState('');
	const postType = (select('core/editor') as { getCurrentPostType: () => string })
		.getCurrentPostType();

	const locationViewOptions = [
		{ value: '', label: __("Don't show", 'ctx-events') },
		{ value: 'name', label: __('Name', 'ctx-events') },
		{ value: 'city', label: __('City', 'ctx-events') },
		{ value: 'country', label: __('Country', 'ctx-events') },
		{ value: 'state', label: __('State', 'ctx-events') },
	];

	const speakerViewOptions = [
		{ value: '', label: __("Don't show", 'ctx-events') },
		{ value: 'name', label: __('Name only', 'ctx-events') },
		{ value: 'image', label: __('Name and image', 'ctx-events') },
	];
	const personView = showPerson || '';

	const scopeOptions = [
		{ value: 'future', label: __('Future', 'ctx-events') },
		{ value: 'past', label: __('Past', 'ctx-events') },
		{ value: 'today', label: __('Today', 'ctx-events') },
		{ value: 'tomorrow', label: __('Tomorrow', 'ctx-events') },
		{ value: 'this-month', label: __('This month', 'ctx-events') },
		{ value: 'next-month', label: __('Next month', 'ctx-events') },
	];

	const orderListViewOptions = [
		{ value: 'asc', label: __('Ascending', 'ctx-events') },
		{ value: 'desc', label: __('Descending', 'ctx-events') },
	];

	const filteredCategories = useMemo(() => {
		if (!categoryQuery || categoryQuery.length < 3) {
			return availableCategories;
		}

		return availableCategories.filter((category) =>
			category.name.toLowerCase().includes(categoryQuery.toLowerCase()),
		);
	}, [categoryQuery, availableCategories]);

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				{filteredCategories.length > 0 && (
					<>
						{filteredCategories.length > 10 && (
							<TextControl
								label={__('Category', 'ctx-events')}
								value={categoryQuery}
								onChange={(value) => setCategoryQuery(value)}
							/>
						)}
						{filteredCategories.map((category) => (
							<CheckboxControl
								key={category.id}
								label={category.name}
								checked={selectedCategory.includes(category.id)}
								onChange={(value) => {
									setAttributes({
										selectedCategory: value
											? [...selectedCategory, category.id]
											: selectedCategory.filter((id) => id !== category.id),
									});
								}}
							/>
						))}
					</>
				)}
				<FormTokenField
					label={__('Tags', 'ctx-events')}
					value={tagsFieldValue}
					suggestions={tagNames}
					onChange={(selectedTags) => {
						const selectedTagsArray = selectedTags
							.map((tagName) => tagList.find((tag) => tag.name === tagName)?.id)
							.filter((tagId): tagId is number => tagId !== undefined);

						setAttributes({ selectedTags: selectedTagsArray });
					}}
					__experimentalExpandOnFocus
				/>
				<SelectControl
					label={__('Location', 'ctx-events')}
					value={selectedLocation}
					options={locationList}
					onChange={(value) => {
						setAttributes({ selectedLocation: Number.parseInt(value, 10) || 0 });
					}}
				/>
				<SelectControl
					label={__('Scope', 'ctx-events')}
					value={scope}
					options={scopeOptions}
					onChange={(value) => {
						setAttributes({ scope: value });
					}}
				/>
				<SelectControl
					label={__('Sorting', 'ctx-events')}
					value={order}
					options={orderListViewOptions}
					onChange={(value) => {
						setAttributes({ order: value });
					}}
				/>
				<RangeControl
					label={__('Limit', 'ctx-events')}
					max={100}
					min={1}
					value={limit}
					onChange={(value) => {
						setAttributes({ limit: Number(value) || 1 });
					}}
				/>
				{postType === 'ctx-event' && (
					<CheckboxControl
						label={__('Exclude current event', 'ctx-events')}
						checked={excludeCurrent}
						onChange={(value) => setAttributes({ excludeCurrent: value })}
						help={__(
							'If applicable, exclude the current event from the list',
							'events',
						)}
					/>
				)}
			</PanelBody>
			<PanelBody title={__('Filter', 'ctx-events')}>
				<CheckboxControl
					label={__('Show category filter', 'ctx-events')}
					checked={showCategoryFilter}
					onChange={(value) => setAttributes({ showCategoryFilter: value })}
				/>
				<CheckboxControl
					label={__('Show tag filter', 'ctx-events')}
					checked={showTagFilter}
					onChange={(value) => setAttributes({ showTagFilter: value })}
				/>
				<CheckboxControl
					label={__('Show search bar', 'ctx-events')}
					checked={showSearch}
					onChange={(value) => setAttributes({ showSearch: value })}
				/>
				<RadioControl
					label={__('Position', 'ctx-events')}
					help={__('May not apply on mobile phones', 'ctx-events')}
					options={[
						{ label: __('Top', 'ctx-events'), value: 'top' },
						{ label: __('Side', 'ctx-events'), value: 'side' },
					]}
					selected={filterPosition}
					onChange={(value) =>
						setAttributes({ filterPosition: value as 'top' | 'side' })
					}
				/>
			</PanelBody>
			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<label className="components-base-control__label" htmlFor="upcoming-style">
					{__('Style', 'ctx-events')}
				</label>
				<br />
				<div className="styleSelector">
					<Button
						onClick={() => setAttributes({ view: 'mini' })}
						className={view === 'mini' ? 'active' : ''}
					>
						<Icon size="64" className="icon" icon={icons.mini} />
						<div>{__('Table', 'ctx-events')}</div>
					</Button>
					<Button
						onClick={() => setAttributes({ view: 'list' })}
						className={view === 'list' ? 'active' : ''}
					>
						<Icon size="64" className="icon" icon={icons.list} />
						<div>{__('List', 'ctx-events')}</div>
					</Button>
					<Button
						onClick={() => setAttributes({ view: 'cards' })}
						className={view === 'cards' ? 'active' : ''}
					>
						<Icon size="64" className="icon" icon={icons.cards} />
						<div>{__('Cards', 'ctx-events')}</div>
					</Button>
				</div>
				<CheckboxControl
					label={__('Let user select style', 'ctx-events')}
					checked={userStylePicker}
					onChange={(value) => setAttributes({ userStylePicker: value })}
				/>
				<SelectControl
					label={__('Location', 'ctx-events')}
					value={showLocation}
					options={locationViewOptions}
					onChange={(value) => {
						setAttributes({ showLocation: value });
					}}
				/>
				<SelectControl
					label={__('Show Person', 'ctx-events')}
					value={personView}
					options={speakerViewOptions}
					onChange={(value) => {
						setAttributes({ showPerson: value });
					}}
				/>
				<RangeControl
					label={__('Length of preview text', 'ctx-events')}
					max={200}
					min={0}
					help={__('Number of words', 'ctx-events')}
					onChange={(value) => {
						setAttributes({ excerptLength: Number(value) || 0 });
					}}
					value={excerptLength}
				/>
				<PanelRow>
					<CheckboxControl
						label={__('Show audience', 'ctx-events')}
						checked={showAudience}
						onChange={(value) => setAttributes({ showAudience: value })}
					/>
				</PanelRow>
				<PanelRow>
					<CheckboxControl
						label={__('Show image', 'ctx-events')}
						checked={showImages}
						onChange={(value) => setAttributes({ showImages: value })}
					/>
				</PanelRow>
				<PanelRow>
					<CheckboxControl
						label={__('Show category', 'ctx-events')}
						checked={showCategory}
						onChange={(value) => setAttributes({ showCategory: value })}
					/>
				</PanelRow>
				<CheckboxControl
					label={__('Show if event is booked up or nearly booked up', 'ctx-events')}
					checked={showBookedUp}
					onChange={(value) => setAttributes({ showBookedUp: value })}
				/>
				<RangeControl
					label={__('Warning threshold', 'ctx-events')}
					value={bookedUpWarningThreshold}
					onChange={(value) =>
						setAttributes({ bookedUpWarningThreshold: Number(value) || 0 })
					}
					min={0}
					max={10}
					help={__(
						'Show a warning that the event is nearly booked up when only this number of spaces are left',
						'events',
					)}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
