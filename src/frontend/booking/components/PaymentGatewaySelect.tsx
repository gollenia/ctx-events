import { __ } from '@wordpress/i18n';
import { InputField } from '@contexis/wp-react-form';

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
			<InputField
				type="radio"
				name="gateway"
				label={__('Payment method', 'ctx-events')}
				width={6}
				options={Object.fromEntries(
					gateways.map((gateway) => [gateway.id, gateway.title]),
				)}
				value={selectedGateway}
				status="LOADED"
				formTouched={false}
				disabled={false}
				onChange={(value) => onChange(String(value))}
			/>
		</div>
	);
}
