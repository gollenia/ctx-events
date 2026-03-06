import type { DataStatusItem } from './types';

export type StatusItemConfig<TStatus extends string = string> = {
	value: TStatus;
	label: string;
	showEmpty?: boolean;
};

export const mapStatusItems = <TStatus extends string>(
	statuses: Array<StatusItemConfig<TStatus>>,
	apiCounts: Record<string, number> = {},
): Array<DataStatusItem> => {
	return statuses.map((status) => ({
		value: status.value,
		label: status.label,
		count: apiCounts[status.value] ?? 0,
		showEmpty: status.showEmpty,
	}));
};
