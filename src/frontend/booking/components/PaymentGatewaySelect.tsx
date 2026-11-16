import { __ } from '@wordpress/i18n';
import { Radio } from '@contexis/wp-react-form';

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
		<div
			className="booking-gateway-select"
			data-testid="booking-payment-gateways"
		>
			<Radio
				name="gateway"
				label={__('Payment method', 'ctx-events')}
				width={6}
				options={Object.fromEntries(
					gateways.map((gateway) => [gateway.id, gateway.title]),
				)}
				value={selectedGateway}
				onChange={(value) => onChange(String(value))}
			/>
		</div>
	);
}
