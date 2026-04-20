import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Button, InputField } from '@contexis/wp-react-form';
import Chevron from '../../../shared/icons/Chevron';

type Props = {
	content: string;
	currency: string;
	donationAmount: number;
	onChange: (amount: number) => void;
};

const DONATION_PRESETS = [5, 10, 20];

function normalizeDonationAmount(value: string): number {
	const sanitized = value.replace(',', '.').trim();
	if (sanitized === '') {
		return 0;
	}

	const parsed = Number(sanitized);
	if (!Number.isFinite(parsed) || parsed <= 0) {
		return 0;
	}

	return Math.round(parsed * 100);
}

export function DonationAdvertisement({
	content,
	currency,
	donationAmount,
	onChange,
}: Props) {
	const [isOpen, setIsOpen] = useState(donationAmount > 0);
	const [draftAmount, setDraftAmount] = useState(
		donationAmount > 0 ? String((donationAmount / 100).toFixed(2)) : '',
	);

	useEffect(() => {
		setDraftAmount(
			donationAmount > 0 ? String((donationAmount / 100).toFixed(2)) : '',
		);
		setIsOpen(donationAmount > 0);
	}, [donationAmount]);

	return (
		<div className="booking-donation-advertisement">
			<p className="booking-donation-advertisement__eyebrow">
				{__('Support this event', 'ctx-events')}
			</p>
			<div
				className="booking-donation-advertisement__content"
				// eslint-disable-next-line react/no-danger
				dangerouslySetInnerHTML={{ __html: content }}
			/>
			<div className="booking-donation-advertisement__actions">
				<Button
					variant="secondary"
					className={[
						'booking-donation-advertisement__toggle',
						isOpen ? 'booking-donation-advertisement__toggle--open' : '',
					]
						.filter(Boolean)
						.join(' ')}
					onClick={() => setIsOpen((open) => !open)}
				>
					<span>
						{donationAmount > 0
							? __('Change contribution', 'ctx-events')
							: __('Add contribution', 'ctx-events')}
					</span>
					<Chevron
						className="booking-donation-advertisement__chevron"
						open={isOpen}
					/>
				</Button>
				{donationAmount > 0 && (
					<button
						type="button"
						className="booking-donation-advertisement__clear"
						onClick={() => {
							onChange(0);
							setDraftAmount('');
							setIsOpen(false);
						}}
					>
						{__('Remove contribution', 'ctx-events')}
					</button>
				)}
			</div>
			{isOpen && (
				<div className="booking-donation-advertisement__panel">
					<div className="booking-donation-advertisement__presets">
						{DONATION_PRESETS.map((preset) => (
							<button
								key={preset}
								type="button"
								className={
									normalizeDonationAmount(draftAmount) === preset * 100
										? 'booking-donation-advertisement__preset booking-donation-advertisement__preset--active'
										: 'booking-donation-advertisement__preset'
								}
								onClick={() => setDraftAmount(preset.toFixed(2))}
							>
								{preset} {currency}
							</button>
						))}
					</div>
					<label className="booking-donation-advertisement__field">
						<InputField
							type="number"
							name="donation-amount"
							label={__('Your contribution', 'ctx-events')}
							width={6}
							min="0"
							value={draftAmount}
							placeholder="0.00"
							status="LOADED"
							formTouched={false}
							disabled={false}
							help={sprintf(__('Amount in %s.', 'ctx-events'), currency)}
							onChange={(value) => setDraftAmount(String(value))}
						/>
					</label>
					<div className="booking-donation-advertisement__footer">
						<Button
							variant="primary"
							onClick={() => onChange(normalizeDonationAmount(draftAmount))}
						>
							{__('Apply contribution', 'ctx-events')}
						</Button>
					</div>
				</div>
			)}
		</div>
	);
}
