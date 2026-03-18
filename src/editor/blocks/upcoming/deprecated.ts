import save from './save';

const deprecated = [
	{
		attributes: {
			showImages: { type: 'boolean', default: true },
			roundImages: { type: 'boolean', default: false },
			imageSize: { type: 'number', default: 100 },
			showCategory: { type: 'boolean', default: true },
			showLocation: { type: 'string', default: '' },
			filterPosition: { type: 'string', default: 'top' },
			view: { type: 'string', default: 'cards' },
			limit: { type: 'number', default: 99 },
			order: { type: 'string', default: 'ASC' },
			selectedCategory: { type: 'array', default: [] },
			selectedLocation: { type: 'number', default: 0 },
			selectedTags: { type: 'array', default: [] },
			excerptLength: { type: 'number', default: 20 },
			showAudience: { type: 'boolean', default: false },
			showSpeaker: { type: 'string', default: '' },
			showCategoryFilter: { type: 'boolean', default: false },
			showTagFilter: { type: 'boolean', default: false },
			showSearch: { type: 'boolean', default: false },
			filterStyle: { type: 'string', default: 'pills' },
			userStylePicker: { type: 'boolean', default: false },
			scope: { type: 'string', default: 'future' },
			bookedUpWarningThreshold: { type: 'number', default: 0 },
			showBookedUp: { type: 'boolean', default: true },
			excludeCurrent: { type: 'boolean', default: true },
			altText: { type: 'string', default: '' },
		},
		migrate: (attributes: Record<string, unknown>) => ({
			...attributes,
			order:
				typeof attributes.order === 'string'
					? attributes.order.toLowerCase()
					: 'asc',
			showPerson:
				typeof attributes.showSpeaker === 'string' ? attributes.showSpeaker : '',
		}),
		save,
	},
];

export default deprecated;
