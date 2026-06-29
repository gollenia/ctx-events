import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import type { DataTableAction } from '@events/datatable/types';
import ActionModal from '../shared/ActionModal';

type Props = {
	action: DataTableAction;
	item: { id: number };
	onClose: () => void;
	onActionPerformed?: (items: Array<any>) => void;
};

const BulkDuplicateCouponModal = ({
	action,
	item,
	onClose,
	onActionPerformed,
}: Props) => {
	const [count, setCount] = useState('10');
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleConfirm = async () => {
		setIsSubmitting(true);
		try {
			await action.callback(
				[item],
				onActionPerformed,
				{
					count: Math.max(1, parseInt(count, 10) || 1),
				},
			);
			onClose();
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<ActionModal
			title={__('Bulk duplicate coupon', 'ctx-events')}
			onClose={onClose}
			isBusy={isSubmitting}
			onConfirm={handleConfirm}
			footer={
				<>
					<button
						type="button"
						className="components-button is-secondary"
						onClick={onClose}
						disabled={isSubmitting}
					>
						{__('Cancel', 'ctx-events')}
					</button>
					<button
						type="button"
						className="components-button is-primary"
						onClick={handleConfirm}
						disabled={isSubmitting}
					>
						{__('Create copies', 'ctx-events')}
					</button>
				</>
			}
		>
			<TextControl
				label={__('Number of copies', 'ctx-events')}
				type="number"
				min="1"
				step="1"
				value={count}
				onChange={setCount}
				disabled={isSubmitting}
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
		</ActionModal>
	);
};

export default BulkDuplicateCouponModal;
