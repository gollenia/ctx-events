import { formatPrice } from '@events/i18n';
import { Notice, Panel, PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type {
	BookingDetail,
	BookingTransactionResource,
} from 'src/types/types';
import StatusPill, { type StatusPillState } from '../../../shared/status-pill';
import SmallDataTable, {
	type SmallDataTableField,
} from '../../../shared/small-data-table';

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
	isSendingPaymentLink: boolean;
	onSendPaymentLink: () => Promise<{
		transaction: BookingTransactionResource;
		warnings: string[];
	}>;
};

const gatewayLabel = (gateway: string) =>
	({
		mollie: __('Mollie', 'ctx-events'),
		offline: __('Offline', 'ctx-events'),
	}[gateway] ?? gateway);

const TransactionSection = ({
	booking,
	isResolvingPaymentLink,
	onResolvePaymentLink,
	isSendingPaymentLink,
	onSendPaymentLink,
}: Props) => {
	const [paymentLinkError, setPaymentLinkError] = useState<string | null>(null);
	const [paymentLinkMessage, setPaymentLinkMessage] = useState<string | null>(
		null,
	);

	const canResolvePaymentLink =
		booking.gatewaySupportsCheckoutLink &&
		booking.price.finalPrice.amountCents > 0 &&
		booking.status === 1;
	const transactionFields: SmallDataTableField<BookingTransactionResource>[] = [
		{
			id: 'amount',
			label: __('Amount', 'ctx-events'),
			render: (transaction) => formatPrice(transaction.amount),
		},
		{
			id: 'status',
			label: __('Status', 'ctx-events'),
			render: (transaction) => {
				const status = statusConfig[transaction.status] ?? {
					label: String(transaction.status),
					state: 'info' as const,
				};

				return <StatusPill state={status.state}>{status.label}</StatusPill>;
			},
		},
		{
			id: 'gateway',
			label: __('Type', 'ctx-events'),
			render: (transaction) => gatewayLabel(transaction.gateway),
		},
		{
			id: 'reference',
			label: __('Transaction ID / Reference', 'ctx-events'),
			render: (transaction) =>
				transaction.gateway === 'offline'
					? booking.reference
					: transaction.externalId || '—',
		},
	];

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

	const handleSendPaymentLink = async () => {
		setPaymentLinkError(null);
		setPaymentLinkMessage(null);

		try {
			const { transaction, warnings } = await onSendPaymentLink();
			if (!transaction.checkoutUrl) {
				throw new Error(__('No checkout URL was returned.', 'ctx-events'));
			}

			if (warnings.length > 0) {
				setPaymentLinkError(warnings.join(' '));
				return;
			}

			setPaymentLinkMessage(__('Payment link sent.', 'ctx-events'));
		} catch (error: unknown) {
			setPaymentLinkError(
				error instanceof Error
					? error.message
					: __('Could not send payment link.', 'ctx-events'),
			);
		}
	};

	return (
		<Panel header={__('Transactions', 'ctx-events')}>
			<PanelBody>
				<div className="booking-edit__section-header">
					{canResolvePaymentLink && (
						<>
							<button
								type="button"
								className="components-button is-secondary"
								onClick={handleResolvePaymentLink}
								disabled={isResolvingPaymentLink || isSendingPaymentLink}
							>
								{isResolvingPaymentLink
									? __('Resolving…', 'ctx-events')
									: __('Get payment link', 'ctx-events')}
							</button>
							<button
								type="button"
								className="components-button is-primary"
								onClick={handleSendPaymentLink}
								disabled={isResolvingPaymentLink || isSendingPaymentLink}
							>
								{isSendingPaymentLink
									? __('Sending…', 'ctx-events')
									: __('Send payment link', 'ctx-events')}
							</button>
						</>
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

				<SmallDataTable
					items={booking.transactions}
					fields={transactionFields}
					getRowKey={(transaction, index) =>
						transaction.externalId ||
						`${transaction.gateway}-${transaction.createdAt}-${index}`
					}
					className="widefat striped booking-edit__transaction-table"
					emptyMessage={__('No transactions recorded.', 'ctx-events')}
				/>
			</PanelBody>
		</Panel>
	);
};

export default TransactionSection;
