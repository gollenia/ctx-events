import { formatPrice } from '@events/i18n';
import { ExternalLink, Notice, Panel, PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type {
	BookingDetail,
	BookingTransactionResource,
} from 'src/types/types';

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
	return iban
		.replace(/\s/g, '')
		.replace(/(.{4})/g, '$1 ')
		.trim();
}

function formatTransactionDate(value: string): string {
	return new Date(value).toLocaleString(undefined, {
		dateStyle: 'medium',
		timeStyle: 'short',
	});
}

function TransactionCard({
	transaction,
	index,
}: {
	transaction: BookingTransactionResource;
	index: number;
}) {
	const hasBankData =
		transaction.bankData &&
		(transaction.bankData.accountHolder ||
			transaction.bankData.bankName ||
			transaction.bankData.iban ||
			transaction.bankData.bic);

	const summaryId = renderValue(transaction.externalId);

	return (
		<PanelBody
			className="booking-edit__transaction-card"
			title={
				<div className="booking-edit__transaction-summary">
					<strong>{transaction.gateway}</strong>
					<span className="booking-edit__transaction-summary-id">
						{__('Transaction', 'ctx-events')}: {summaryId}
					</span>
				</div>
			}
			initialOpen={false}
		>
			<div className="booking-edit__transaction-content">
				<div className="booking-edit__transaction-list">
					<div className="booking-edit__transaction-row">
						<span className="booking-edit__transaction-label">
							{__('Status', 'ctx-events')}
						</span>
						<strong>
							{STATUS_LABELS[transaction.status] ?? String(transaction.status)}
						</strong>
					</div>
					<div className="booking-edit__transaction-row">
						<span className="booking-edit__transaction-label">
							{__('Amount', 'ctx-events')}
						</span>
						<strong>{formatPrice(transaction.amount)}</strong>
					</div>
					<div className="booking-edit__transaction-row">
						<span className="booking-edit__transaction-label">
							{__('Date', 'ctx-events')}
						</span>
						<strong>{formatTransactionDate(transaction.createdAt)}</strong>
					</div>
					<div className="booking-edit__transaction-row">
						<span className="booking-edit__transaction-label">
							{__('External ID', 'ctx-events')}
						</span>
						<strong>{renderValue(transaction.externalId)}</strong>
					</div>
					{transaction.checkoutUrl ? (
						<div className="booking-edit__transaction-row">
							<span className="booking-edit__transaction-label">
								{__('Payment link', 'ctx-events')}
							</span>
							<ExternalLink href={transaction.checkoutUrl}>
								{__('Open payment', 'ctx-events')}
							</ExternalLink>
						</div>
					) : null}
					{hasBankData ? (
						<div className="booking-edit__transaction-row">
							<span className="booking-edit__transaction-label">
								{__('Bank transfer', 'ctx-events')}
							</span>
							<strong>
								{transaction.bankData.accountHolder ||
									transaction.bankData.bankName ||
									formatIban(transaction.bankData.iban ?? '') ||
									__('Details available', 'ctx-events')}
							</strong>
						</div>
					) : null}
				</div>

				{transaction.instructions ? (
					<div className="booking-edit__transaction-block">
						<span className="booking-edit__transaction-label">
							{__('Instructions', 'ctx-events')}
						</span>
						<p>{transaction.instructions}</p>
					</div>
				) : null}

				{hasBankData ? (
					<div className="booking-edit__transaction-block">
						<div className="booking-edit__transaction-list">
							<div className="booking-edit__transaction-row">
								<span className="booking-edit__transaction-label">
									{__('Account Holder', 'ctx-events')}
								</span>
								<strong>
									{renderValue(transaction.bankData.accountHolder)}
								</strong>
							</div>
							<div className="booking-edit__transaction-row">
								<span className="booking-edit__transaction-label">
									{__('Bank', 'ctx-events')}
								</span>
								<strong>{renderValue(transaction.bankData.bankName)}</strong>
							</div>
							<div className="booking-edit__transaction-row">
								<span className="booking-edit__transaction-label">
									{__('IBAN', 'ctx-events')}
								</span>
								<strong>
									{renderValue(formatIban(transaction.bankData.iban ?? ''))}
								</strong>
							</div>
							<div className="booking-edit__transaction-row">
								<span className="booking-edit__transaction-label">
									{__('BIC', 'ctx-events')}
								</span>
								<strong>{renderValue(transaction.bankData.bic)}</strong>
							</div>
						</div>
					</div>
				) : null}
			</div>
		</PanelBody>
	);
}

const TransactionSection = ({
	booking,
	isResolvingPaymentLink,
	onResolvePaymentLink,
}: Props) => {
	const [paymentLinkError, setPaymentLinkError] = useState<string | null>(null);
	const [paymentLinkMessage, setPaymentLinkMessage] = useState<string | null>(
		null,
	);

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
		<Panel header={__('Transactions', 'ctx-events')}>
			<PanelBody>
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
					<p className="booking-edit__empty">
						{__('No transactions recorded.', 'ctx-events')}
					</p>
				) : (
					<div className="booking-edit__transactions">
						{booking.transactions.map((transaction, index) => (
							<TransactionCard
								key={`${transaction.externalId || transaction.createdAt}-${index}`}
								index={index}
								transaction={transaction}
							/>
						))}
					</div>
				)}
			</PanelBody>
		</Panel>
	);
};

export default TransactionSection;
