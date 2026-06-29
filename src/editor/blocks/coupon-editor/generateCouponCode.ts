const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
const CODE_LENGTH = 10;

export const generateCouponCode = (): string => {
	let code = '';

	for (let index = 0; index < CODE_LENGTH; index++) {
		const randomIndex = Math.floor(Math.random() * ALPHABET.length);
		code += ALPHABET[randomIndex];
	}

	return code;
};
