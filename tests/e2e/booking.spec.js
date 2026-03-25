const { test, expect } = require('@playwright/test');

const HOST_PAGE = process.env.PLAYWRIGHT_BOOKING_PAGE;
const FREE_POST_ID = 101;
const PAID_POST_ID = 202;

const registrationFields = [
	{
		name: 'first_name',
		label: 'First name',
		required: true,
		width: 3,
		description: null,
		visibilityRule: null,
		type: 'input',
		inputType: 'text',
	},
	{
		name: 'last_name',
		label: 'Last name',
		required: true,
		width: 3,
		description: null,
		visibilityRule: null,
		type: 'input',
		inputType: 'text',
	},
	{
		name: 'email',
		label: 'Email',
		required: true,
		width: 6,
		description: null,
		visibilityRule: null,
		type: 'input',
		inputType: 'email',
	},
];

const registrationValues = {
	first_name: 'Max',
	last_name: 'Mustermann',
	email: 'max@example.com',
};

const attendeeFields = [
	{
		name: 'attendee_name',
		label: 'Attendee name',
		required: true,
		width: 6,
		description: null,
		visibilityRule: null,
		type: 'input',
		inputType: 'text',
	},
];

function createBookingData({
	postId,
	eventName,
	ticketId,
	ticketName,
	priceAmountCents,
	couponsEnabled = false,
	includeAttendees = false,
	gateways = [],
}) {
	return {
		eventName,
		eventStartDate: '2026-04-10 18:00:00',
		eventEndDate: '2026-04-10 20:00:00',
		eventDescription: 'Test event',
		tickets: [
			{
				id: ticketId,
				name: ticketName,
				price: {
					amountCents: priceAmountCents,
					currency: 'EUR',
				},
				available_quantity: 25,
				ticket_limit_per_booking: 4,
				booking_limit: 4,
			},
		],
		gateways,
		bookingForm: {
			id: postId * 10,
			type: 'booking',
			name: 'Booking form',
			description: null,
			fields: registrationFields,
		},
		attendeeForm: includeAttendees
			? {
					id: postId * 10 + 1,
					type: 'attendee',
					name: 'Attendee form',
					description: null,
					fields: attendeeFields,
				}
			: null,
		couponsEnabled,
		token: `token-${postId}`,
	};
}

const freeBookingData = createBookingData({
	postId: FREE_POST_ID,
	eventName: 'Free Test Event',
	ticketId: 'free-ticket',
	ticketName: 'Community Ticket',
	priceAmountCents: 0,
});

const paidBookingData = createBookingData({
	postId: PAID_POST_ID,
	eventName: 'Paid Test Event',
	ticketId: 'paid-ticket',
	ticketName: 'Premium Ticket',
	priceAmountCents: 4900,
	couponsEnabled: true,
	includeAttendees: true,
	gateways: [
		{ id: 'manual', title: 'Bank Transfer' },
		{ id: 'card', title: 'Credit Card' },
	],
});

async function registerBookingApiMocks(page) {
	await page.route('**/wp-json/events/v3/events/*/prepare-booking**', async (route) => {
		const url = new URL(route.request().url());
		const match = url.pathname.match(/\/events\/v3\/events\/(\d+)\/prepare-booking$/);
		const postId = Number(match?.[1] ?? 0);

		if (postId === FREE_POST_ID) {
			await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify(freeBookingData) });
			return;
		}

		if (postId === PAID_POST_ID) {
			await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify(paidBookingData) });
			return;
		}

		await route.fulfill({ status: 404, contentType: 'application/json', body: JSON.stringify({ message: 'Unknown test event.' }) });
	});

	await page.route('**/wp-json/events/v3/bookings**', async (route) => {
		const payload = route.request().postDataJSON();

		if (payload.event_id === FREE_POST_ID) {
			await route.fulfill({
				status: 200,
				contentType: 'application/json',
				body: JSON.stringify({
					reference: 'FREE-12345',
					payment: null,
					customerEmailStatus: 'sent',
				}),
			});
			return;
		}

		if (payload.event_id === PAID_POST_ID) {
			await route.fulfill({
				status: 200,
				contentType: 'application/json',
				body: JSON.stringify({
					reference: 'PAID-98765',
					payment: {
						gateway: 'manual',
						status: 10,
						amount: {
							amountCents: 4900,
							currency: 'EUR',
						},
						bankData: {
							accountHolder: 'Contexis Events',
							iban: 'AT611904300234573201',
							bic: 'BKAUATWW',
							bankName: 'Test Bank',
						},
						instructions: 'Please transfer the amount within 7 days.',
					},
					customerEmailStatus: 'sent',
				}),
			});
			return;
		}

		await route.fulfill({ status: 400, contentType: 'application/json', body: JSON.stringify({ message: 'Unexpected booking payload.' }) });
	});

	await page.route('**/wp-json/events/v3/coupons/check**', async (route) => {
		const url = new URL(route.request().url());
		const code = url.searchParams.get('code');

		if (code === 'INVALID') {
			await route.fulfill({
				status: 400,
				contentType: 'application/json',
				body: JSON.stringify({
					message: 'Coupon code is invalid.',
				}),
			});
			return;
		}

		await route.fulfill({
			status: 200,
			contentType: 'application/json',
			body: JSON.stringify({
				name: 'TEST10',
				discount_type: 'fixed',
				discount_value: 1000,
				discount_amount: 1000,
			}),
		});
	});
}

async function openBooking(page, postId) {
	await page.goto(HOST_PAGE);
	await page.evaluate((resolvedPostId) => {
		document.dispatchEvent(
			new CustomEvent('ctx:booking:open', {
				detail: { postId: resolvedPostId },
			}),
		);
	}, postId);

	await expect(page.getByTestId('booking-modal')).toBeVisible();
}

async function selectTicket(page, ticketId) {
	await page.getByTestId(`booking-ticket-${ticketId}-increment`).click();
	await page.getByTestId('booking-tickets-continue').click();
}

async function fillFieldByName(container, fieldName, value) {
	const field = container.getByTestId(`booking-field-${fieldName}`);
	const control = field.locator('input, textarea, select').first();

	if (typeof value === 'boolean') {
		if (value) {
			await control.check();
		} else {
			await control.uncheck();
		}
		return;
	}

	await control.fill(String(value));
}

async function fillRegistration(page) {
	const section = page.getByTestId('booking-section-registration');

	for (const [fieldName, value] of Object.entries(registrationValues)) {
		await fillFieldByName(section, fieldName, value);
	}

	await section.getByTestId('booking-registration-continue').click();
}

async function fillAttendees(page) {
	const section = page.getByTestId('booking-section-attendees');
	const attendee = section.getByTestId('booking-attendee-0');
	await fillFieldByName(attendee, 'attendee_name', 'Erika Beispiel');
	await section.getByTestId('booking-attendees-continue').click();
}

async function applyCoupon(page, code) {
	const coupon = page.getByTestId('booking-coupon');
	await coupon.locator('#booking-coupon-code').fill(code);
	await coupon.getByRole('button', { name: 'Check coupon' }).click();
}

test.describe('booking app', () => {
	test.beforeEach(async ({ page, baseURL }) => {
		test.skip(
			!baseURL || !HOST_PAGE,
			'Set PLAYWRIGHT_BASE_URL and PLAYWRIGHT_BOOKING_PAGE to run booking Playwright tests.',
		);

		await registerBookingApiMocks(page);
	});

	test('completes a free booking flow with mocked backend', async ({ page }) => {
		await openBooking(page, FREE_POST_ID);
		await selectTicket(page, 'free-ticket');
		await fillRegistration(page);

		await expect(page.getByText('Confirm booking')).toBeVisible();
		await page.locator('.booking-consent__checkbox').check();
		await page.getByTestId('booking-submit').click();

		await expect(page.getByTestId('booking-section-success')).toBeVisible();
		await expect(page.getByText('Booking confirmed!')).toBeVisible();
		await expect(page.getByText('FREE-12345')).toBeVisible();
	});

	test('shows the paid booking flow with attendees and payment summary', async ({ page }) => {
		await openBooking(page, PAID_POST_ID);
		await selectTicket(page, 'paid-ticket');
		await fillAttendees(page);
		await fillRegistration(page);

		await expect(page.getByTestId('booking-section-payment')).toBeVisible();
		const priceSummary = page.getByTestId('booking-price-summary');
		await expect(priceSummary).toBeVisible();
		await expect(page.getByTestId('booking-payment-gateways')).toBeVisible();
		await expect(page.getByLabel('Credit Card')).toBeVisible();
		await expect(priceSummary.getByText(/49[,.]00/).first()).toBeVisible();
		await page.locator('.booking-consent__checkbox').check();
		await page.getByTestId('booking-submit').click();

		await expect(page.getByTestId('booking-section-success')).toBeVisible();
		await expect(page.getByText('PAID-98765', { exact: true })).toBeVisible();
		await expect(page.getByText('Please transfer the amount within 7 days.')).toBeVisible();
	});

	test('applies a coupon discount in the paid booking flow', async ({ page }) => {
		await openBooking(page, PAID_POST_ID);
		await selectTicket(page, 'paid-ticket');
		await fillAttendees(page);
		await fillRegistration(page);

		await expect(page.getByTestId('booking-price-summary')).toBeVisible();
		await applyCoupon(page, 'TEST10');

		await expect(page.getByText(/Coupon "TEST10" is valid\./)).toBeVisible();
		await expect(page.getByText('Coupon discount')).toBeVisible();
		await expect(page.getByText('Total due')).toBeVisible();
		await expect(page.getByText(/39[,.]00/)).toBeVisible();
	});

	test('shows an error for an invalid coupon code', async ({ page }) => {
		await openBooking(page, PAID_POST_ID);
		await selectTicket(page, 'paid-ticket');
		await fillAttendees(page);
		await fillRegistration(page);

		await expect(page.getByTestId('booking-coupon')).toBeVisible();
		await applyCoupon(page, 'INVALID');

		await expect(
			page.getByText('Coupon code is invalid.'),
		).toBeVisible();
		await expect(page.getByText('Coupon discount')).toHaveCount(0);
	});
});
