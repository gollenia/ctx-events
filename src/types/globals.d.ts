declare module '@events/i18n' {
	export function formatDate(date: string): string;
	export function formatDateRange(start: string, end: string): string;
	export function formatTime(date: string): string;
	export function formatTimeRange(start: string, end: string): string;
}

declare global {
	interface Window {
		ctxIcons?: Record<string, string>;
		eventBlocksLocalization?: {
			locale: string;
		};
		eventEditorLocalization: {
			bookingEnabled: boolean;
			currency: string;
		};
	}
}

declare module '@wordpress/block-editor';

export type Embedded = {
	_embedded: {
		'wp:featuredmedia'?: Array<{
			media_details?: {
				sizes?: {
					thumbnail?: {
						source_url?: string;
					};
				};
			};
		}>;
	};
};
