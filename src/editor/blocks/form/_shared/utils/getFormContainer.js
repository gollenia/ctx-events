import { store as blockEditorStore } from '@wordpress/block-editor';

/**
 * @param {Function} select - Die 'select' Funktion von useSelect
 * @param {string} clientId - Die eigene ID
 */
const getFormContainer = (select, clientId) => {
    const { getBlockParentsByBlockName, getBlock } = select(blockEditorStore);
    
    const CONTAINER_NAME = 'ctx-events/form-container';

    const parentIds = getBlockParentsByBlockName(clientId, CONTAINER_NAME);
    
    const containerId = parentIds?.[parentIds.length - 1];

    if (!containerId) return null;

    return getBlock(containerId);
};

export default getFormContainer;