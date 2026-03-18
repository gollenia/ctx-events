declare module '@events/i18n' {
	export function formatDate(date: string): string;
	export function formatDateRange(start: string, end: string): string;
	export function formatTime(date: string): string;
	export function formatTimeRange(start: string, end: string): string;
}

declare global {
	interface Window {
		eventBlocksLocalization?: {
			locale: string;
		};
		eventEditorLocalization: {
			bookingEnabled: boolean;
			currency: string;
		};
	}
}

export {};

declare module '@wordpress/block-editor';
