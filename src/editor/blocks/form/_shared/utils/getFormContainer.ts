import { store as blockEditorStore } from '@wordpress/block-editor';
import type { SelectFunction } from '@wordpress/data';

const getFormContainer = (select: SelectFunction, clientId: string) => {
	const { getBlockParentsByBlockName, getBlock } = select(blockEditorStore);

	const CONTAINER_NAME = 'ctx-events/form-container';

	const parentIds = getBlockParentsByBlockName(clientId, CONTAINER_NAME);

	const containerId = parentIds?.[parentIds.length - 1];

	if (!containerId) return null;

	return getBlock(containerId);
};

export default getFormContainer;
