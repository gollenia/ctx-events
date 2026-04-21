import domReady from '@wordpress/dom-ready';

const BOOKING_OPEN_EVENT = 'ctx:booking:open';

type BookingOpenDetail = {
	postId: number;
};

function publishBookingOpen(detail: BookingOpenDetail): void {
	document.dispatchEvent(
		new CustomEvent<BookingOpenDetail>(BOOKING_OPEN_EVENT, { detail }),
	);
}

function initBookingTriggers(): void {
	const triggerButtons = document.querySelectorAll<HTMLElement>(
		'[data-ctx-booking-trigger="true"]',
	);

	const handleClick = (event: Event) => {
		const button = event.currentTarget as HTMLElement | null;
		const postId = Number(button?.dataset.ctxEventId ?? '0');
		if (!postId) {
			return;
		}

		publishBookingOpen({ postId });
	};

	for (const button of triggerButtons) {
		button.addEventListener('click', handleClick);
	}
}

domReady(() => {
	initBookingTriggers();
});
