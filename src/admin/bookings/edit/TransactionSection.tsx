import { Notice } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { formatPrice } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import type { BookingDetail, BookingTransactionResource } from 'src/types/types';

const STATUS_LABELS: Record<number, string> = {
	0: __('Pending', 'ctx-events'),
	1: __('Paid', 'ctx-events'),
	2: __('Failed', 'ctx-events'),
	3: __('Expired', 'ctx-events'),
	4: __('Refunded', 'ctx-events'),
	5: __('Canceled', 'ctx-events'),
};

type Props = {
	booking: BookingDetail;
	isResolvingPaymentLink: boolean;
	onResolvePaymentLink: () => Promise<BookingTransactionResource>;
};

function renderValue(value: string | null | undefined) {
	return value && value.trim() !== '' ? value : '—';
}

function formatIban(iban: string): string {
	return iban.replace(/\s/g, '').replace(/(.{4})/g, '$1 ').trim();
}

function TransactionCard({
	transaction,
}: {
	transaction: BookingTransactionResource;
}) {
	return (
		<article className="booking-edit__transaction-card">
			<div className="booking-edit__transaction-grid">
				<div>
					<span className="booking-edit__transaction-label">{__('Gateway', 'ctx-events')}</span>
					<strong>{transaction.gateway}</strong>
				</div>
				<div>
					<span className="booking-edit__transaction-label">{__('Status', 'ctx-events')}</span>
					<strong>{STATUS_LABELS[transaction.status] ?? String(transaction.status)}</strong>
				</div>
				<div>
					<span className="booking-edit__transaction-label">{__('Amount', 'ctx-events')}</span>
					<strong>{formatPrice(transaction.amount)}</strong>
				</div>
				<div>
					<span className="booking-edit__transaction-label">{__('Date', 'ctx-events')}</span>
					<strong>{new Date(transaction.createdAt).toLocaleString()}</strong>
				</div>
				<div>
					<span className="booking-edit__transaction-label">{__('External ID', 'ctx-events')}</span>
					<strong>{renderValue(transaction.externalId)}</strong>
				</div>
				<div>
					<span className="booking-edit__transaction-label">{__('Checkout URL', 'ctx-events')}</span>
					<strong className="booking-edit__transaction-link">
						{transaction.checkoutUrl ? (
							<a href={transaction.checkoutUrl} target="_blank" rel="noreferrer">
								{transaction.checkoutUrl}
							</a>
						) : (
							'—'
						)}
					</strong>
				</div>
			</div>

			{transaction.instructions && (
				<div className="booking-edit__transaction-block">
					<span className="booking-edit__transaction-label">{__('Instructions', 'ctx-events')}</span>
					<p>{transaction.instructions}</p>
				</div>
			)}

			{transaction.bankData && (
				<div className="booking-edit__transaction-grid">
					<div>
						<span className="booking-edit__transaction-label">{__('Account holder', 'ctx-events')}</span>
						<strong>{transaction.bankData.accountHolder}</strong>
					</div>
					<div>
						<span className="booking-edit__transaction-label">{__('Bank', 'ctx-events')}</span>
						<strong>{transaction.bankData.bankName}</strong>
					</div>
					<div>
						<span className="booking-edit__transaction-label">{__('IBAN', 'ctx-events')}</span>
						<strong>{formatIban(transaction.bankData.iban)}</strong>
					</div>
					<div>
						<span className="booking-edit__transaction-label">{__('BIC', 'ctx-events')}</span>
						<strong>{transaction.bankData.bic}</strong>
					</div>
				</div>
			)}
		</article>
	);
}

const TransactionSection = ({
	booking,
	isResolvingPaymentLink,
	onResolvePaymentLink,
}: Props) => {
	const [paymentLinkError, setPaymentLinkError] = useState<string | null>(null);
	const [paymentLinkMessage, setPaymentLinkMessage] = useState<string | null>(null);

	const canResolvePaymentLink =
		booking.gatewaySupportsCheckoutLink &&
		booking.price.finalPrice.amountCents > 0;

	const handleResolvePaymentLink = async () => {
		setPaymentLinkError(null);
		setPaymentLinkMessage(null);

		try {
			const transaction = await onResolvePaymentLink();
			if (!transaction.checkoutUrl) {
				throw new Error(__('No checkout URL was returned.', 'ctx-events'));
			}

			let message = __('Payment link generated.', 'ctx-events');

			if (navigator.clipboard?.writeText) {
				await navigator.clipboard.writeText(transaction.checkoutUrl);
				message = __('Payment link copied to clipboard.', 'ctx-events');
			}

			setPaymentLinkMessage(message);
		} catch (error: any) {
			setPaymentLinkError(
				error?.message ?? __('Could not resolve payment link.', 'ctx-events'),
			);
		}
	};

	return (
	<section className="booking-edit__section">
		<div className="booking-edit__section-header">
			<h3>{__('Transactions', 'ctx-events')}</h3>
			{canResolvePaymentLink && (
				<button
					type="button"
					className="components-button is-secondary"
					onClick={handleResolvePaymentLink}
					disabled={isResolvingPaymentLink}
				>
					{isResolvingPaymentLink
						? __('Resolving…', 'ctx-events')
						: __('Get payment link', 'ctx-events')}
				</button>
			)}
		</div>

		{paymentLinkMessage && (
			<Notice status="success" isDismissible={false}>
				{paymentLinkMessage}
			</Notice>
		)}

		{paymentLinkError && (
			<Notice status="error" isDismissible={false}>
				{paymentLinkError}
			</Notice>
		)}

		{booking.transactions.length === 0 ? (
			<p className="booking-edit__empty">{__('No transactions recorded.', 'ctx-events')}</p>
		) : (
			<div className="booking-edit__transactions">
				{booking.transactions.map((transaction, index) => (
					<TransactionCard
						key={`${transaction.externalId || transaction.createdAt}-${index}`}
						transaction={transaction}
					/>
				))}
			</div>
		)}
	</section>
	);
};

export default TransactionSection;
