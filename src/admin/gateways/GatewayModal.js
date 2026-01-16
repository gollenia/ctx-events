import apiFetch from '@wordpress/api-fetch';
import { Button, Modal } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import AdminField from '../../shared/adminfields/AdminField';

const GatewayModal = ({ slug, onCancel }) => {
	const [gateway, setGateway] = useState({});
	const [loading, setLoading] = useState(true);

	console.log(slug)
	useEffect(() => {
		if (slug === '') {
			return;
		}
		apiFetch({ path: `/events/v3/gateways/${slug}` })
			.then((data) => {
				console.log(data);
				setGateway(data);
				setLoading(false);
			})
			.catch((err) => {
				console.error(err);
			});
	}, [slug]);

	const onSave = () => {
		apiFetch({
			path: `/events/v3/gateways/${slug}`,
			method: 'POST',
			data: { slug, settings: gateway.settings },
		})
			.then((data) => {
				console.log(data);
				onCancel();
			})
			.catch((err) => {
				console.error('Fehler beim Speichern des Gateways:', err);
			});
	};

	console.log('GatewayModal', slug, loading, gateway);

	return (
		<>
			{slug && !loading && (
				<Modal
					onRequestClose={onCancel}
					title={__('Edit Gateway', 'events')}
					size="medium"
				>
					<div className="events-ticket-modal-content">
						{Array.isArray(gateway?.settings) &&
							gateway?.settings?.map((field, index) => {
								return (
									<AdminField
										{...field}
										key={index}
										onChange={(value) => {
											setGateway((prev) => {
												const updatedSettings = [...prev.settings];
												updatedSettings[index] = {
													...updatedSettings[index],
													value,
												};

												return {
													...prev,
													settings: updatedSettings,
												};
											});
										}}
									/>
								);
							})}
						<div className="modal-actions">
							<Button onClick={() => {}} variant="secondary">
								Cancel
							</Button>
							<Button onClick={onSave} variant="primary">
								OK
							</Button>
						</div>
					</div>
				</Modal>
			)}
		</>
	);
};

export default GatewayModal;
