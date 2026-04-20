export type Address = {
readonly streetAddress: string | null,
readonly addressLocality: string | null,
readonly postalCode: string | null,
readonly addressRegion: string | null,
readonly addressCountry: string | null,
};
export type AvailableTicketResource = {
readonly id: string,
readonly name: string,
readonly price: number,
};
export type BankTransferPayment = {
readonly gateway: string,
readonly status: number,
readonly amount: Array<any>,
readonly bankData: Array<any>,
readonly instructions: string,
};
export type BookingAttendeeResource = {
readonly ticketId: string,
readonly name: PersonName | null,
readonly metadata: Array<any>,
};
export type BookingDenyReason = "disabled" | "no_capacity" | "not_started" | "ended" | "sold_out" | "form_error" | "no_tickets";
export type BookingDetail = {
readonly reference: string,
readonly date: string,
readonly status: number,
readonly gateway: string | null,
readonly gatewaySupportsCheckoutLink: boolean,
readonly event: BookingEventResource,
readonly registration: Array<any>,
readonly attendees: BookingAttendeeResource[],
readonly transactions: BookingTransactionResource[],
readonly price: PriceSummary,
readonly bookingForm: Array<any>,
readonly attendeeForm: Array<any> | null,
readonly notes: BookingNoteResource[],
readonly logEntries: BookingLogEntryResource[],
readonly availableTickets: AvailableTicketResource[],
};
export type BookingEventResource = {
readonly id: number,
readonly title: string,
};
export type BookingInfo = {
eventName: string,
eventStartDate: string,
eventEndDate: string,
eventDescription: string,
tickets: Array<any>,
gateways: Array<any>,
bookingForm: Array<any>,
attendeeForm: Array<any> | null,
couponsEnabled: boolean,
donationEnabled: boolean,
donationAdvertisement: string | null,
token: string,
};
export type BookingListItem = {
readonly reference: string,
readonly email: undefined,
readonly name: PersonName,
readonly event: {
id: number,
title: string,
},
readonly status: number,
readonly priceSummary: PriceSummary,
readonly spaces: number,
readonly gateway: {
slug: string,
name: string,
} | null,
readonly date: string,
readonly transactionId: string | null,
readonly transactionExpiresAt: string | null,
};
export type BookingLogEntryResource = {
readonly eventType: string,
readonly level: string,
readonly timestamp: string,
readonly actorId: number,
readonly actorName: string,
readonly message: string | null,
};
export type BookingLogEvent = "created" | "updated" | "deleted" | "approved" | "rejected" | "cancelled" | "restored" | "email_warning";
export type BookingLogLevel = "info" | "warning" | "error";
export type BookingNoteResource = {
readonly text: string,
readonly date: string,
readonly author: string,
};
export type BookingStatus = 1 | 2 | 3 | 4;
export type BookingTransactionResource = {
readonly gateway: string,
readonly status: number,
readonly amount: Array<any>,
readonly externalId: string,
readonly createdAt: string,
readonly bankData: Array<any> | null,
readonly instructions: string,
readonly checkoutUrl: string | null,
readonly gatewayUrl: string | null,
};
export type CheckboxVariant = "default" | "switch";
export type DatabaseOutput = "OBJECT" | "ARRAY_A" | "ARRAY_N";
export type DiscountType = "percent" | "fixed";
export type EmailTarget = "customer" | "admin" | "billing_contact" | "event_contact";
export type EmailTemplateKey = "booking_pending_manual" | "booking_created_online" | "booking_confirmed_manual" | "booking_confirmed_online" | "booking_offline_expiring" | "booking_offline_expired" | "booking_payment_failed" | "booking_denied" | "booking_cancelled" | "admin_booking_pending_manual" | "admin_booking_created_online";
export type EmailTrigger = "booking_pending_manual" | "booking_created_online" | "booking_confirmed_manual" | "booking_confirmed_online" | "booking_offline_expiring" | "booking_offline_expired" | "booking_payment_failed" | "booking_denied" | "booking_cancelled";
export type ErrorType = "ERROR" | "WARNING" | "INFO";
export type Event = {
readonly id: number,
readonly name: string,
readonly description: string | null,
readonly status: string,
readonly startDate: string,
readonly endDate: string | null,
readonly audience: string | null,
readonly bookingSummary: EventBookingSummary | null,
readonly includes: EventIncludes | null,
readonly schema: Schema | null,
};
export type EventBookingSummary = {
readonly isBookable: boolean,
readonly denyReason: string | null,
readonly approved: number,
readonly available: number | null,
readonly pending: number | null,
readonly totalCapacity: number | null,
readonly lowestAvailablePrice: Price | null,
readonly lowestPrice: Price | null,
readonly highestPrice: Price | null,
readonly bookingStart: string | null,
readonly bookingEnd: string | null,
};
export type EventIncludes = {
image: undefined | null,
location: Location | null,
person: undefined | null,
categories: Array<any> | null,
tags: Array<any> | null,
};
export type EventOrderBy = "date" | "title" | "booking_start" | "booking" | "location" | "person" | "price";
export type EventStatus = "draft" | "publish" | "future" | "pending" | "private" | "trash" | "cancelled";
export type FieldType = "input" | "textarea" | "select" | "checkbox" | "html" | "country" | "date" | "number";
export type FieldWidth = 1 | 2 | 3 | 4 | 5 | 6;
export type Form = {
readonly id: number,
readonly title: string,
readonly description: string | null,
readonly type: string,
readonly createdAt: string,
readonly tags: undefined,
readonly status: string,
readonly usageCount: number,
};
export type FormType = "booking" | "attendee";
export type Gateway = {
readonly slug: string,
readonly title: string,
readonly adminName: string,
readonly enabled: boolean,
readonly supportsCheckoutLink: boolean,
readonly settings: any[],
readonly isValid: boolean,
};
export type InputType = "email" | "tel" | "url" | "text" | "number" | "date";
export type Location = {
id: number,
link: undefined,
name: string,
address: Address,
geoCoordinates: Record<string, number> | null,
};
export type MailTemplate = {
readonly key: string,
readonly label: string,
readonly description: string,
readonly trigger: string,
readonly target: string,
readonly source: string,
readonly isCustomized: boolean,
readonly enabled: boolean,
readonly subject: string | null,
readonly body: string,
readonly replyTo: string | null,
readonly recipientConfig: {
sendToEventContact: boolean,
sendToEventPerson: boolean,
sendToBookingAdmin: boolean,
sendToWpAdmin: boolean,
customRecipients: string[],
} | null,
};
export type NumberVariant = "input" | "slider";
export type Order = "asc" | "desc";
export type PaymentProvider = "offline" | "mollie";
export type PersonName = {
readonly firstName: string,
readonly lastName: string,
readonly prefix: string | null,
readonly suffix: string | null,
};
export type Price = {
readonly amountCents: number,
readonly currency: string,
};
export type PriceSummary = {
readonly bookingPrice: Price,
readonly donationAmount: Price,
readonly discountAmount: Price,
readonly finalPrice: Price,
};
export type RedirectPayment = {
readonly gateway: string,
readonly status: number,
readonly amount: Array<any>,
readonly externalId: string,
readonly checkoutUrl: string,
readonly gatewayUrl: string | null,
readonly instructions: string,
};
export type Schema = {
context: string,
type: string,
id: string,
};
export type SelectVariant = "radio" | "select" | "combobox";
export type Status = "publish" | "future" | "draft" | "private" | "trash";
export type Ticket = {
readonly id: string,
readonly name: string,
readonly price: undefined,
readonly availableQuantity: number,
readonly ticketLimitPerBooking: number | null,
readonly remainingTickets: number | null,
readonly remainingOverallCapacity: number | null,
readonly bookingLimit: number | null,
};
export type TimeScope = "all" | "future" | "past" | "today" | "tomorrow" | "one-week" | "this-week" | "this-month" | "next-month" | "1-months" | "2-months" | "3-months" | "this-year" | "1-year";
export type TransactionStatus = 0 | 1 | 2 | 3 | 4 | 5;
export type ValidationError = "required" | "invalid_format" | "too_low" | "too_high" | "empty";
