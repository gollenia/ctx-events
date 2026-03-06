export const extractContextOptions = (
	fields: Array<{ context: string }>,
): Array<string> => {
	const map = new Map<string, string>();
	for (const f of fields) {
		if (!f.context) continue;
		const key = f.context.toLowerCase();
		if (!map.has(key)) map.set(key, f.context);
	}
	return Array.from(map.values()).map((value) => value);
};
