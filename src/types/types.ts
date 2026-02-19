export type BookingDenyReason = "disabled" | "no_capacity" | "not_started" | "ended" | "sold_out" | "form_error" | "no_tickets";
export type TimeScope = "all" | "future" | "past" | "today" | "tomorrow" | "week" | "this-week" | "this-month" | "next-month" | "1-months" | "2-months" | "3-months" | "6-months" | "12-months" | "year";
export type EventStatus = "draft" | "publish" | "future" | "pending" | "private" | "trash" | "cancelled";
export type EventBookingSummary = {
readonly isBookable: boolean
readonly denyReason: string | null
readonly totalBookedCount: number
readonly totalAvailableCount: number | null
readonly totalCapacity: number | null
readonly lowestAvailablePrice: Price | null
readonly lowestPrice: Price | null
readonly highestPrice: Price | null
readonly bookingStart: string | null
readonly bookingEnd: string | null
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
export type BookingStatus = 1 | 2 | 3 | 4 | 9;
export type PaymentProvider = "offline" | "mollie";
export type DiscountType = "percent" | "fixed";
export type TransactionStatus = 0 | 1 | 2 | 3 | 4 | 5;
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
