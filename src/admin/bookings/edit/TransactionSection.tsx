import { formatPrice } from '@events/i18n';
import { ExternalLink, Notice, Panel, PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type {
	BookingDetail,
	BookingTransactionResource,
} from 'src/types/types';
import StatusPill, { type StatusPillState } from '../../../shared/status-pill';

const statusConfig: Record<number, { label: string; state: StatusPillState }> =
	{
		0: { label: __('Pending', 'ctx-events'), state: 'warning' },
		1: { label: __('Paid', 'ctx-events'), state: 'success' },
		2: { label: __('Failed', 'ctx-events'), state: 'error' },
		3: { label: __('Expired', 'ctx-events'), state: 'warning' },
		4: { label: __('Refunded', 'ctx-events'), state: 'info' },
		5: { label: __('Canceled', 'ctx-events'), state: 'error' },
	};

type Props = {
	booking: BookingDetail;
	isResolvingPaymentLink: boolean;
	onResolvePaymentLink: () => Promise<BookingTransactionResource>;
};

const renderValue = (value: string | null | undefined) =>
	value && value.trim() !== '' ? value : '—';

const formatIban = (iban: string) =>
	iban
		.replace(/\s/g, '')
		.replace(/(.{4})/g, '$1 ')
		.trim();

const formatTransactionDate = (value: string) =>
	new Date(value).toLocaleString(undefined, {
		dateStyle: 'medium',
		timeStyle: 'short',
	});

const TransactionDetails = ({
	transaction,
}: {
	transaction: BookingTransactionResource;
}) => {
	const bankData = transaction.bankData;
	const bankDetails = bankData
		? [
				[__('Account holder', 'ctx-events'), bankData.accountHolder],
				[__('Bank', 'ctx-events'), bankData.bankName],
				[__('IBAN', 'ctx-events'), formatIban(bankData.iban ?? '')],
				[__('BIC', 'ctx-events'), bankData.bic],
			].filter(([, value]) => Boolean(value))
		: [];

	if (!transaction.instructions && bankDetails.length === 0) {
		return '—';
	}

	return (
		<div className="booking-edit__transaction-details">
			{transaction.instructions && <p>{transaction.instructions}</p>}
			{bankDetails.length > 0 && (
				<dl>
					{bankDetails.map(([label, value]) => (
						<div key={label}>
							<dt>{label}</dt>
							<dd>{value}</dd>
						</div>
					))}
				</dl>
			)}
		</div>
	);
};

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
		} catch (error: unknown) {
			setPaymentLinkError(
				error instanceof Error
					? error.message
					: __('Could not resolve payment link.', 'ctx-events'),
			);
		}
	};

	return (
		<Panel header={__('Transactions', 'ctx-events')}>
			<PanelBody>
				<div className="booking-edit__section-header">
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
					<div className="booking-edit__transaction-table-wrap">
						<table className="widefat striped booking-edit__transaction-table">
							<thead>
								<tr>
									<th>{__('Gateway', 'ctx-events')}</th>
									<th>{__('Status', 'ctx-events')}</th>
									<th>{__('Amount', 'ctx-events')}</th>
									<th>{__('Date', 'ctx-events')}</th>
									<th>{__('Transaction', 'ctx-events')}</th>
									<th>{__('Details', 'ctx-events')}</th>
								</tr>
							</thead>
							<tbody>
								{booking.transactions.map((transaction, index) => {
									const status = statusConfig[transaction.status] ?? {
										label: String(transaction.status),
										state: 'info' as const,
									};

									return (
										<tr
											key={`${transaction.externalId || transaction.createdAt}-${index}`}
										>
											<td>{transaction.gateway}</td>
											<td>
												<StatusPill state={status.state}>
													{status.label}
												</StatusPill>
											</td>
											<td>{formatPrice(transaction.amount)}</td>
											<td>{formatTransactionDate(transaction.createdAt)}</td>
											<td>
												<strong>{renderValue(transaction.externalId)}</strong>
												{transaction.checkoutUrl && (
													<div className="booking-edit__transaction-action">
														<ExternalLink href={transaction.checkoutUrl}>
															{__('Open payment', 'ctx-events')}
														</ExternalLink>
													</div>
												)}
											</td>
											<td>
												<TransactionDetails transaction={transaction} />
											</td>
										</tr>
									);
								})}
							</tbody>
						</table>
					</div>
				)}
			</PanelBody>
		</Panel>
	);
};

export default TransactionSection;
