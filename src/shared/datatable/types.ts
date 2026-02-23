export type DataViewConfig = {
	type?: 'table' | 'grid' | 'list';
	search?: string;
	filters: Array<DataFilterField>;
	page: number;
	perPage: number;
	sort: { field: string; direction: 'asc' | 'desc' };
	titleField?: string;
	mediaField?: string;
	descriptionField?: string;
	showTitle?: boolean;
	showMedia?: boolean;
	showDescription?: boolean;
	showLevels?: boolean;
	groupBy?: {
		field: string;
		direction: 'asc' | 'desc';
	};
	fields: Array<string>;
	layout?: Object;
};

export type DataFilterField = {
	field: string;
	operator: string;
	value: string | number | boolean | Array<string | number>;
	isLocked?: boolean;
};

export type DataFilterConfig<T = string> = {
	id: string;
	label: string;
	type:
		| 'array'
		| 'text'
		| 'boolean'
		| 'color'
		| 'date'
		| 'datetime'
		| 'email'
		| 'integer'
		| 'number'
		| 'url'
		| 'password'
		| 'media';
	elements?: Array<DataFilterElement<T>>;
	filterBy?: {
		operators: Array<
			| 'after'
			| 'afterInc'
			| 'before'
			| 'beforeInc'
			| 'between'
			| 'contains'
			| 'greaterThan'
			| 'greaterThanOrEqual'
			| 'inThePast'
			| 'isAll'
			| 'isAny'
			| 'isNone'
			| 'is'
			| 'isNot'
			| 'lessThan'
			| 'lessThanOrEqual'
			| 'notContains'
			| 'notOn'
			| 'on'
			| 'over'
			| 'startsWith'
		>;
	};
};

export type DataFilterElement<T = string> = {
	value: T;
	label: string;
};

export type DataTableAction = {
	id: string;
	label: string | (() => string);
	supportsBulk?: boolean;
	isPrimary?: boolean;
	disabled?: boolean;
	context?: 'list' | 'single';
	callback: (
		items: Array<any>,
		onActionPerformed?: (items: Array<any>) => void,
	) => void;
	RenderModal?: React.ComponentType<{
		action: DataTableAction;
		onClose: () => void;
	}>;
	modalHeader?: string | (() => string);
	modalSize?: 'small' | 'medium' | 'large';
	modalFocusOnMount?: boolean | string;
	delete?: boolean;
};

export type DataPaginationInfo = {
	totalItems: number;
	totalPages: number;
};

export type DataFieldConfig = {
	id: string;
	label: string;
	enableSorting?: boolean;
	className?: string;
	getValue?: (item: any) => any;
	filterBy?: DataFilterConfig;
	render?: (item: any) => React.ReactElement;
	isVisible?: boolean;
	enableHiding?: boolean;
};

export type DataStatusItem = {
	label: string;
	value: string;
	count: number;
	showEmpty?: boolean;
};
