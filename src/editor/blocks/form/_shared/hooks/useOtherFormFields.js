import { useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import getFormContainer from '../utils/getFormContainer';

/**
 * @param {string} clientId - Die ID des aktuellen Blocks
 * @returns {Array} Liste der Felder [{label: 'Name', value: 'name'}]
 */
export default function useOtherFormFields(clientId) {
	return useSelect((select) => {
		const container = getFormContainer(select, clientId);

		if (!container) return [];

		return container.innerBlocks
			.filter((block) => block.clientId !== clientId)
			.filter((block) => block.attributes?.name)
			.map((block) => {
				return {
					label: block.attributes.label || block.attributes.name,
					value: block.attributes.name
				};
			});

	}, [clientId]);
}