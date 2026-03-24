import { __ } from '@wordpress/i18n';

import type { GatewayInfo } from '../types';

type Props = {
	gateways: GatewayInfo[];
	selectedGateway: string;
	onChange: (gateway: string) => void;
};

export function PaymentGatewaySelect({
	gateways,
	selectedGateway,
	onChange,
}: Props) {
	if (gateways.length <= 1) {
		return null;
	}

	return (
		<fieldset
			className="booking-gateway-select"
			data-testid="booking-payment-gateways"
		>
			<legend className="booking-gateway-select__legend">
				{__('Payment method', 'ctx-events')}
			</legend>
			{gateways.map((gateway) => (
				<label key={gateway.id} className="booking-gateway-select__option">
					<input
						type="radio"
						name="gateway"
						value={gateway.id}
						checked={selectedGateway === gateway.id}
						onChange={() => onChange(gateway.id)}
					/>
					{gateway.title}
				</label>
			))}
		</fieldset>
	);
}
