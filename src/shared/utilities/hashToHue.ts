function hashToHue(input: string): number {
	let hash = 0;
	for (let i = 0; i < input.length; i++) {
		hash = (hash * 31 + input.charCodeAt(i)) | 0; // int32
	}
	return Math.abs(hash) % 360;
}

export default hashToHue;
