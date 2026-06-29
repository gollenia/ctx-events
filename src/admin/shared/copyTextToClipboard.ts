const fallbackCopy = (text: string) => {
	const input = document.createElement('input');
	input.value = text;
	document.body.append(input);
	input.select();
	document.execCommand('copy');
	input.remove();
};

export const copyTextToClipboard = async (text: string): Promise<boolean> => {
	try {
		if (navigator.clipboard?.writeText) {
			try {
				await navigator.clipboard.writeText(text);
				return true;
			} catch {
				fallbackCopy(text);
				return true;
			}
		}

		fallbackCopy(text);
		return true;
	} catch {
		return false;
	}
};
