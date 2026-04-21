export default function truncate(
	text: string,
	maxWords: number,
	ellipsis = '...',
) {
	if (text.length === 0 || maxWords === 0) {
		return '';
	}

	const textArray = text.split(' ');
	if (textArray.length <= maxWords) {
		return text;
	}

	return `${textArray.slice(0, maxWords).join(' ')}${ellipsis}`;
}
