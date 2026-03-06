import { store as blockEditorStore } from '@wordpress/block-editor';
import type { BlockInstance } from '@wordpress/blocks';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import getFormContainer from '../utils/getFormContainer';
import { isSlugLocked } from '../utils/validation';

const useDependencyLock: (clientId: string, fieldName: string) => boolean = (
	clientId,
	fieldName,
) => {
	const isSystemLocked = isSlugLocked(fieldName);

	const isReferenced = useSelect(
		(select) => {
			if (!fieldName) return false;

			const container = getFormContainer(select, clientId);

			if (!container || !container.innerBlocks) return false;

			return container.innerBlocks.some(
				(block: BlockInstance) =>
					block.clientId !== clientId &&
					block.attributes?.visibilityField === fieldName,
			);
		},
		[clientId, fieldName],
	);

	const shouldBeLocked = isSystemLocked || isReferenced;

	const { updateBlockAttributes } = useDispatch(blockEditorStore);

	const currentLock = useSelect(
		(select) => {
			return select(blockEditorStore).getBlock(clientId)?.attributes?.lock
				?.remove;
		},
		[clientId],
	);

	useEffect(() => {
		if (shouldBeLocked && !currentLock) {
			updateBlockAttributes(clientId, { lock: { remove: true, move: false } });
		} else if (!shouldBeLocked && currentLock) {
			updateBlockAttributes(clientId, { lock: { remove: false, move: false } });
		}
	}, [shouldBeLocked, currentLock, clientId, updateBlockAttributes]);

	return isReferenced;
};

export default useDependencyLock;
