import { BlockList } from '@wordpress/block-editor';
import type { BlockInstance } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import getFormContainer from '../utils/getFormContainer';

type NamedBlock = BlockInstance & {
	attributes: { name: string; label?: string; context?: string };
};

const isNamedBlock = (block: BlockInstance): block is NamedBlock =>
	typeof (block as NamedBlock)?.attributes?.name === 'string' &&
	(block as NamedBlock).attributes.name.length > 0;

const useOtherFormFields: (
	clientId: string,
) => Array<{ label: string; value: string; context: string; type: string }> = (
	clientId,
) => {
	return useSelect(
		(select) => {
			const container = getFormContainer(select, clientId);
			if (!container) return [];

			const innerBlocks = container.innerBlocks as unknown as BlockInstance[];

			return innerBlocks
				.filter((block) => block.clientId !== clientId)
				.filter(isNamedBlock)
				.map((block) => ({
					label: block.attributes.label ?? block.attributes.name,
					type: block.name,
					value: block.attributes.name,
					context: block.attributes.context ?? '',
				}));
		},
		[clientId],
	);
};

export default useOtherFormFields;
