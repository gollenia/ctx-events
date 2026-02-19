import { InspectorControls } from '@wordpress/block-editor';
import {
	CheckboxControl,
	FormTokenField,
	PanelBody,
	PanelRow,
	RangeControl,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Inspector = (props) => {
	const {
		attributes: {
			limit,
			columnsSmall,
			columnsMedium,
			columnsLarge,
			showImages,
			dropShadow,
			style,
			showCategory,
			showLocation,
			roundImages,
			excerptLength,
			selectedCategory,
			selectedLocation,
			fromDate,
			toDate,
			order,
			showAudience,
			showSpeaker,
		},
		tagList,
		categoryList,
		tagsFieldValue,
		locationList,
		tagNames,
		setAttributes,
	} = props;

	const locationViewOptions = [
		{ value: '', label: __('', 'ctx-events') },
		{ value: 'city', label: __('City', 'ctx-events') },
		{ value: 'name', label: __('Name', 'ctx-events') },
	];

	const orderListViewOptions = [
		{ value: 'asc', label: __('Ascending', 'ctx-events') },
		{ value: 'desc', label: __('Descending', 'ctx-events') },
	];

	return (
		<InspectorControls>
			<PanelBody title={__('Data', 'ctx-events')} initialOpen={true}>
				<SelectControl
					label={__('Category', 'ctx-events')}
					value={selectedCategory}
					options={categoryList}
					onChange={(value) => {
						setAttributes({ selectedCategory: value });
					}}
				/>
				<FormTokenField
					label={__('Tags', 'ctx-events')}
					value={tagsFieldValue}
					suggestions={tagNames}
					onChange={(selectedTags) => {
						const selectedTagsArray = [];
						selectedTags.map((tagName) => {
							const matchingTag = tagList.find((tag) => {
								return tag.name === tagName;
							});
							if (matchingTag !== undefined) {
								selectedTagsArray.push(matchingTag.id);
							}
						});

						setAttributes({ selectedTags: selectedTagsArray });
					}}
					__experimentalExpandOnFocus={true}
				/>

				<SelectControl
					label={__('Location', 'ctx-events')}
					value={selectedLocation}
					options={locationList}
					onChange={(value) => {
						setAttributes({ selectedLocation: value });
					}}
				/>
			</PanelBody>

			<PanelBody title={__('Appearance', 'ctx-events')} initialOpen={true}>
				<PanelRow>
					<ToggleControl
						label={__('Drop shadow', 'ctx-events')}
						checked={dropShadow}
						onChange={(value) => setAttributes({ dropShadow: value })}
					/>
				</PanelRow>

				<PanelRow>
					<SelectControl
						label={__('Location', 'ctx-events')}
						value={showLocation}
						options={locationViewOptions}
						onChange={(value) => {
							setAttributes({ showLocation: value });
						}}
					/>
				</PanelRow>
				<RangeControl
					label={__('Length of preview text', 'ctx-events')}
					max={200}
					min={0}
					help={__('Number of words', 'ctx-events')}
					onChange={(value) => {
						setAttributes({ excerptLength: value });
					}}
					value={excerptLength}
				/>
				<PanelRow>
					<CheckboxControl
						label={__('Show Audience', 'ctx-events')}
						checked={showAudience}
						onChange={(value) => setAttributes({ showAudience: value })}
					/>
				</PanelRow>
				<PanelRow>
					<CheckboxControl
						label={__('Show Speaker', 'ctx-events')}
						checked={showSpeaker}
						onChange={(value) => setAttributes({ showSpeaker: value })}
					/>
				</PanelRow>

				<PanelRow>
					<CheckboxControl
						label={__('Show category', 'ctx-events')}
						checked={showCategory}
						onChange={(value) => setAttributes({ showCategory: value })}
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
