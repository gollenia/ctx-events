import apiFetch from '@wordpress/api-fetch';
import { Button, FlexItem, Modal, Flex } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import AdminField from '../../shared/adminfields/AdminField';


const GatewayModal = ({ slug, onCancel }) => {
	const [gateway, setGateway] = useState({});
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);

	const collectSettings = () => {
		const settings = {};
		gateway.settings.forEach((field) => {
			settings[field.name] = field.value;
		});
		return settings;
	};

	useEffect(() => {
		if (slug === '') {
			return;
		}
		apiFetch({ path: `/events/v3/gateways/${slug}` })
			.then((data) => {
				setGateway(data);
				setLoading(false);
			})
			.catch((err) => {
				console.error(err);
			});
	}, [slug]);

	const onSave = () => {
		setSaving(true);
		apiFetch({
			path: `/events/v3/gateways/${slug}`,
			method: 'PUT',
			data: { settings: collectSettings() },
		})
			.then((data) => {
				console.log('save data:', data);
				onCancel();
				setSaving(false);
			})
			.catch((err) => {
				console.error('Fehler beim Speichern des Gateways:', err);
				setSaving(false);
			});
	};

	return (
		<>
			{slug && !loading && (
				<Modal
					onRequestClose={onCancel}
					title={__('Edit Gateway', 'ctx-events')}
					size="large"
				>
					<form>
						<Flex direction="column" className="events-ticket-modal-content">
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
							<Flex align="right" gap={2} className="events-modal-actions" justify="flex-end">
								<FlexItem>
								<Button onClick={onCancel} variant="secondary">
									{__('Cancel', 'ctx-events')}
								</Button>
								</FlexItem>
								<FlexItem>
								<Button onClick={onSave} variant="primary" isBusy={saving} disabled={saving}>
									{__('Save Changes', 'ctx-events')}
								</Button>
								</FlexItem>
							</Flex>
						</Flex>
					</form>
				</Modal>
			)}
		</>
	);
};

export default GatewayModal;
