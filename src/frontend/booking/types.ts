export type VisibilityRule = {
	field: string;
	value: unknown;
	operator: 'equals' | 'not_equals' | 'not_empty';
};

export type FormField = {
	name: string;
	label: string;
	required: boolean;
	width: 1 | 2 | 3 | 4 | 5 | 6;
	description: string | null;
	visibilityRule: VisibilityRule | null;
	type:
		| 'input'
		| 'textarea'
		| 'select'
		| 'checkbox'
		| 'html'
		| 'country'
		| 'date'
		| 'number';
	// type-specific props merged in by backend
	[key: string]: unknown;
};

export type BookingFormData = {
	id: number;
	type: string;
	name: string;
	description: string | null;
	fields: FormField[];
};

export type TicketInfo = {
	id: string;
	name: string;
	price_in_cents: number;
	currency: string;
	available_quantity: number;
	ticket_limit_per_booking: number | null;
	booking_limit: number | null;
};

export type GatewayInfo = {
	id: string;
	title: string;
};

export type BookingData = {
	eventName: string;
	eventStartDate: string;
	eventEndDate: string;
	eventDescription: string;
	tickets: TicketInfo[];
	gateways: GatewayInfo[];
	bookingForm: BookingFormData;
	attendeeForm: BookingFormData | null;
	couponsEnabled: boolean;
	token: string;
};

export type AttendeePayload = {
	ticket_id: string;
	metadata: Record<string, unknown>;
};

export type BookingState = {
	tickets: Record<string, number>;
	attendees: AttendeePayload[];
	registration: Record<string, unknown>;
	gateway: string;
	couponCode: string;
	openSection: SectionId;
	completedSections: Set<SectionId>;
};

export type SectionId =
	| 'tickets'
	| 'attendees'
	| 'booking'
	| 'payment'
	| 'success';

export type SubmitResult =
	| { type: 'mollie'; url: string }
	| { type: 'success'; reference: string }
	| { type: 'error'; message: string };
