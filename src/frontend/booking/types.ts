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
	price: {
		amountCents: number;
		currency: string;
	};
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
	donationEnabled: boolean;
	donationAdvertisement: string | null;
	token: string;
};

export type AttendeePayload = {
	ticket_id: string;
	metadata: Record<string, unknown>;
};

export type PaymentAmount = {
	amountCents: number;
	currency: string;
};

export type PaymentBankData = {
	accountHolder: string;
	iban: string;
	bic: string;
	bankName: string;
};

export type RedirectPayment = {
	gateway: string;
	status: number;
	amount: PaymentAmount;
	externalId: string;
	checkoutUrl: string;
	gatewayUrl: string | null;
	instructions: string;
};

export type BankTransferPayment = {
	gateway: string;
	status: number;
	amount: PaymentAmount;
	bankData: PaymentBankData;
	instructions: string;
};

export type BookingPayment = RedirectPayment | BankTransferPayment;

export type BookingCreatedResponse = {
	reference: string;
	payment: BookingPayment | null;
	customerEmailStatus?: 'sent' | 'failed' | 'skipped' | 'unknown';
};

export type PaymentQrResponse = {
	gateway: string;
	format: 'svg' | 'png';
	mimeType: string;
	dataUri: string;
};

export type CouponCheckResult = {
	name: string;
	discountType: string;
	discountValue: number;
	discountAmount: number;
};

export type BookingState = {
	attendees: AttendeePayload[];
	registration: Record<string, unknown>;
	gateway: string;
	couponCode: string;
	couponCheckResult: CouponCheckResult | null;
	donationAmount: number;
	openSection: SectionId;
	completedSections: Set<SectionId>;
};

export type PaymentStateUpdates = Partial<
	Pick<
		BookingState,
		'gateway' | 'couponCode' | 'couponCheckResult' | 'donationAmount'
	>
>;

export type SectionId =
	| 'tickets'
	| 'attendees'
	| 'booking'
	| 'payment'
	| 'success';

export type SubmitResult =
	| {
			type: 'success';
			reference: string;
			payment: BookingPayment | null;
			customerEmailStatus: 'sent' | 'failed' | 'skipped' | 'unknown';
	  }
	| { type: 'error'; message: string };
