export type FeaturedSelectionMode = 'manual' | 'query' | 'current';

export type FeaturedEventAttributes = {
	selectedEvent: number;
	selectionMode: FeaturedSelectionMode;
	queryCategoryIds: number[];
	queryTagIds: number[];
	queryLocationId: number;
	queryScope: string;
};

export type FeaturedEventContext = {
	postId?: number;
	postType?: string;
	'ctx-events/eventId'?: number;
	'ctx-events/selectionMode'?: FeaturedSelectionMode;
	'ctx-events/queryCategoryIds'?: number[];
	'ctx-events/queryTagIds'?: number[];
	'ctx-events/queryLocationId'?: number;
	'ctx-events/queryScope'?: string;
};
