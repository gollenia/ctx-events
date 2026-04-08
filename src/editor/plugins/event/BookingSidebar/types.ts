import type { EventMailTemplateOverride } from '@events/emails';

export type BookingMeta = {
	_booking_enabled?: boolean | number;
	_booking_start?: string;
	_booking_end?: string;
	_booking_capacity?: string | number;
	_max_per_booking?: string | number;
	_booking_form?: string | number;
	_attendee_form?: string | number;
	_booking_currency?: string;
	_booking_reference_prefix?: string;
	_booking_reference_suffix?: string;
	_booking_coupons?: number[];
	_booking_mails?: EventMailTemplateOverride[];
	_donation_enabled?: boolean | number;
	_event_tickets?: BookingTicket[];
};

export type BookingTicket = {
	ticket_id: string;
	ticket_name: string;
	ticket_description: string;
	ticket_price: string | number;
	ticket_spaces: string | number;
	ticket_min: string | number;
	ticket_max: string | number;
	ticket_start: string;
	ticket_end: string;
	ticket_enabled: boolean | number;
	ticket_order: number;
	ticket_form: string | number;
};

export type BookingFormOption = {
	value: number;
	label: string;
};

export type BookingSidebarProps = {
	meta: BookingMeta;
	updateMeta: (updates: Partial<BookingMeta>) => void;
	postId: number;
	postType: string;
};

export type EditorLocalization = {
	bookingEnabled?: boolean;
	bookingMessage?: string;
	currency?: string;
};

export const getEventEditorLocalization = (): EditorLocalization => {
	return (
		(window as Window & {
			eventEditorLocalization?: EditorLocalization;
		}).eventEditorLocalization ?? {}
	);
};

export const getDefaultCurrency = (): string => {
	return getEventEditorLocalization().currency ?? 'EUR';
};

export const isBookingEnabled = (meta: BookingMeta): boolean => {
	return Boolean(meta._booking_enabled);
};
