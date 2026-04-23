import { useCallback, useState } from '@wordpress/element';
import type { DataViewConfig } from '@events/datatable/types';

const canUseStorage = (): boolean =>
	typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';

const readStoredView = (
	storageKey: string,
	defaultView: DataViewConfig,
): DataViewConfig => {
	if (!canUseStorage()) {
		return defaultView;
	}

	const storedValue = window.localStorage.getItem(storageKey);
	if (!storedValue) {
		return defaultView;
	}

	try {
		const parsedView = JSON.parse(storedValue);
		if (!parsedView || typeof parsedView !== 'object' || Array.isArray(parsedView)) {
			return defaultView;
		}

		return {
			...defaultView,
			...parsedView,
		};
	} catch {
		return defaultView;
	}
};

export const useStoredView = (
	storageKey: string,
	defaultView: DataViewConfig,
) => {
	const [view, setViewState] = useState<DataViewConfig>(() =>
		readStoredView(storageKey, defaultView),
	);

	const setView = useCallback(
		(updater: DataViewConfig | ((prevView: DataViewConfig) => DataViewConfig)) => {
			setViewState((prevView) => {
				const nextView =
					typeof updater === 'function' ? updater(prevView) : updater;

				if (canUseStorage()) {
					window.localStorage.setItem(storageKey, JSON.stringify(nextView));
				}

				return nextView;
			});
		},
		[storageKey],
	);

	return { view, setView };
};
