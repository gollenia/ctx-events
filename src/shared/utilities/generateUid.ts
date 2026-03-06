const generateUid: (prefix?: string) => string = (prefix = '') =>
	prefix + Math.random().toString(36).slice(2, 12);

export default generateUid;
