import apiFetch from '@wordpress/api-fetch';

declare global {
	interface Window {
		ctxIcons?: Record<string, string>;
	}
}

type IconResponse = {
	icons?: Record<string, string>;
	missing?: string[];
	version?: string;
};

let pendingRequest: Promise<Record<string, string>> | null = null;

const getCache = (): Record<string, string> => {
	window.ctxIcons = window.ctxIcons ?? {};

	return window.ctxIcons;
};

const getMissingIcons = (names: string[]): string[] => {
	const cache = getCache();

	return names.filter((name) => !cache[name]);
};

export async function ensureIcons(names: string[]): Promise<Record<string, string>> {
	const uniqueNames = Array.from(new Set(names.filter(Boolean)));

	if (uniqueNames.length === 0) {
		return getCache();
	}

	const missingNames = getMissingIcons(uniqueNames);

	if (missingNames.length === 0) {
		return getCache();
	}

	if (!pendingRequest) {
		pendingRequest = apiFetch<IconResponse>({
			path: `/events/v3/icons?names=${encodeURIComponent(missingNames.join(','))}`,
		})
			.then((response) => {
				window.ctxIcons = {
					...getCache(),
					...(response.icons ?? {}),
				};

				return getCache();
			})
			.finally(() => {
				pendingRequest = null;
			});
	}

	await pendingRequest;

	const stillMissing = getMissingIcons(uniqueNames);

	if (stillMissing.length > 0) {
		return ensureIcons(stillMissing);
	}

	return getCache();
}
