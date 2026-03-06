import { generateUid } from '@events/utilities';
import { useEffect } from '@wordpress/element';

const useFieldName = (
	attributes: { name?: string },
	setAttributes: (attributes: { name: string }) => void,
) => {
	useEffect(() => {
		if (!attributes.name?.trim()) {
			setAttributes({ name: generateUid('cb_') });
		}
	}, [attributes.name]);
};

export default useFieldName;
