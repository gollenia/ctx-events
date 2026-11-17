import type { FormField } from './types';

const fieldNames: Record<string, string> = {
	first_name: 'given-name',
	firstname: 'given-name',
	vorname: 'given-name',
	last_name: 'family-name',
	lastname: 'family-name',
	nachname: 'family-name',
	address: 'address-line1',
	adresse: 'address-line1',
	street: 'address-line1',
	strasse: 'address-line1',
	street_address: 'address-line1',
	strasse_und_hausnummer: 'address-line1',
	postcode: 'postal-code',
	postalcode: 'postal-code',
	postal_code: 'postal-code',
	zip: 'postal-code',
	plz: 'postal-code',
	postleitzahl: 'postal-code',
	city: 'address-level2',
	town: 'address-level2',
	ort: 'address-level2',
	stadt: 'address-level2',
	wohnort: 'address-level2',
};

export function getFieldAutoComplete(field: FormField): string | undefined {
	if (typeof field['autoComplete'] === 'string' && field['autoComplete']) {
		return field['autoComplete'];
	}
	if (field.type === 'country') return 'country-name';
	if (field.type !== 'input') return undefined;
	if (field['inputType'] === 'email') return 'email';
	if (field['inputType'] === 'tel') return 'tel';
	if (field['inputType'] !== 'text' && field['inputType'] != null)
		return undefined;
	const normalize = (value: string) =>
		value
			.trim()
			.toLowerCase()
			.replace(/ß/g, 'ss')
			.replace(/[\s-]+/g, '_');
	return (
		fieldNames[normalize(field.name)] ?? fieldNames[normalize(field.label)]
	);
}
