export type BookingDenyReason = "disabled" | "no_capacity" | "not_started" | "ended" | "sold_out" | "form_error" | "no_tickets";
export type TimeScope = "all" | "future" | "past" | "today" | "tomorrow" | "one-week" | "this-week" | "this-month" | "next-month" | "1-months" | "2-months" | "3-months" | "this-year" | "1-year";
export type EventOrderBy = "date" | "title" | "booking_start" | "booking" | "location" | "person" | "price";
export type EventStatus = "draft" | "publish" | "future" | "pending" | "private" | "trash" | "cancelled";
export type Ticket = {
readonly id: string
readonly name: string
readonly price: Price
readonly availableQuantity: number
readonly ticketLimitPerBooking: number | null
readonly remainingTickets: number | null
readonly remainingOverallCapacity: number | null
readonly bookingLimit: number | null
};
export type EventBookingSummary = {
readonly isBookable: boolean
readonly denyReason: string | null
readonly approved: number
readonly available: number | null
readonly pending: number | null
readonly totalCapacity: number | null
readonly lowestAvailablePrice: undefined | null
readonly lowestPrice: undefined | null
readonly highestPrice: undefined | null
readonly bookingStart: string | null
readonly bookingEnd: string | null
};
export type BookingInfo = {
eventName: string
eventStartDate: string
eventEndDate: string
eventDescription: string
tickets: []
gateways: []
bookingForm: []
attendeeForm: [] | null
couponsEnabled: boolean
donationEnabled: boolean
token: string
};
export type EventIncludes = {
image: undefined | null
location: Location | null
categories: [] | null
tags: [] | null
};
export type Event = {
readonly id: number
readonly name: string
readonly description: string | null
readonly status: string
readonly startDate: string
readonly endDate: string | null
readonly audience: string | null
readonly bookingSummary: EventBookingSummary | null
readonly includes: EventIncludes | null
readonly schema: Schema | null
};
export type DatabaseOutput = "OBJECT" | "ARRAY_A" | "ARRAY_N";
export type Order = "asc" | "desc";
export type Price = {
readonly amountCents: number
readonly currency: string
};
export type PersonName = {
readonly firstName: string
readonly lastName: string
readonly prefix: string | null
readonly suffix: string | null
};
export type Status = "publish" | "future" | "draft" | "private" | "trash";
export type ErrorType = "ERROR" | "WARNING" | "INFO";
export type Schema = {
context: string
type: string
id: string
};
export type Address = {
readonly streetAddress: string | null
readonly addressLocality: string | null
readonly postalCode: string | null
readonly addressRegion: string | null
readonly addressCountry: string | null
};
export type BookingEvent = "created" | "updated" | "deleted" | "approved" | "rejected" | "cancelled" | "restored";
export type BookingStatus = 1 | 2 | 3 | 4 | 9;
export type BookingNoteResource = {
readonly text: string
readonly date: string
readonly author: string
};
export type BookingDetail = {
readonly reference: string
readonly date: string
readonly status: number
readonly name: PersonName
readonly email: string
readonly gateway: string | null
readonly event: BookingEventResource
readonly registration: []
readonly attendees: BookingAttendeeResource[]
readonly price: BookingPriceSummaryResource
readonly notes: BookingNoteResource[]
readonly availableTickets: AvailableTicketResource[]
};
export type BookingListItem = {
readonly reference: string
readonly email: undefined
readonly name: PersonName
readonly event: []
readonly status: number
readonly price: Price
readonly donation: Price
readonly spaces: number
readonly gateway: [] | null
readonly date: string
};
export type AvailableTicketResource = {
readonly id: string
readonly name: string
readonly price: number
};
export type BookingEventResource = {
readonly id: number
readonly title: string
};
export type BookingAttendeeResource = {
readonly ticketId: string
readonly name: PersonName | null
readonly metadata: []
};
export type BookingPriceSummaryResource = {
readonly bookingPrice: number
readonly donationAmount: number
readonly discountAmount: number
readonly finalPrice: number
readonly currency: string
};
export type PaymentProvider = "offline" | "mollie";
export type DiscountType = "percent" | "fixed";
export type TransactionStatus = 0 | 1 | 2 | 3 | 4 | 5;
export type Gateway = {
readonly slug: string
readonly title: string
readonly adminName: string
readonly enabled: boolean
readonly settings: []
};
export type Location = {
id: number
link: undefined
name: string
address: Address
geoCoordinates: Record<string, number> | null
};
export type ValidationError = "required" | "invalid_format" | "too_low" | "too_high" | "empty";
export type FieldType = "input" | "textarea" | "select" | "checkbox" | "html" | "country" | "date" | "number";
export type SelectVariant = "radio" | "select" | "combobox";
export type CheckboxVariant = "default" | "switch";
export type NumberVariant = "input" | "slider";
export type FormType = "booking" | "attendee";
export type InputType = "email" | "tel" | "url" | "text" | "number" | "date";
export type FieldWidth = 1 | 2 | 3 | 4 | 5 | 6;
export type Form = {
readonly id: number
readonly title: string
readonly description: string | null
readonly type: string
readonly createdAt: string
readonly usageCount: number
readonly tags: undefined
readonly status: string
};
