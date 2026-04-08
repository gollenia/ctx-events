import type { CountryRegion } from './types';

const COUNTRY_REGIONS: Record<string, string[]> = {
	AD: ['EU'], AE: ['AS'], AF: ['AS'], AG: ['NA'], AI: ['NA'],
	AL: ['EU'], AM: ['AS'], AO: ['AF'], AR: ['SA'], AS: ['OC'],
	AT: ['EU', 'DACH'], AU: ['OC'], AW: ['SA'], AZ: ['AS'],
	BA: ['EU'], BB: ['NA'], BD: ['AS'], BE: ['EU'], BF: ['AF'],
	BG: ['EU'], BH: ['AS'], BI: ['AF'], BJ: ['AF'], BL: ['NA'],
	BM: ['NA'], BN: ['AS'], BO: ['SA'], BR: ['SA'], BS: ['NA'],
	BT: ['AS'], BW: ['AF'], BY: ['EU'], BZ: ['NA'],
	CA: ['NA'], CD: ['AF'], CF: ['AF'], CG: ['AF'], CH: ['EU', 'DACH'],
	CI: ['AF'], CK: ['OC'], CL: ['SA'], CM: ['AF'], CN: ['AS'],
	CO: ['SA'], CR: ['NA'], CU: ['NA'], CV: ['AF'], CY: ['EU'],
	CZ: ['EU'],
	DE: ['EU', 'DACH'], DJ: ['AF'], DK: ['EU'], DM: ['NA'], DO: ['NA'],
	DZ: ['AF'],
	EC: ['SA'], EE: ['EU'], EG: ['AF'], ER: ['AF'], ES: ['EU'],
	ET: ['AF'],
	FI: ['EU'], FJ: ['OC'], FK: ['SA'], FM: ['OC'], FO: ['EU'],
	FR: ['EU'],
	GA: ['AF'], GB: ['EU'], GD: ['NA'], GE: ['AS'], GF: ['SA'],
	GH: ['AF'], GI: ['EU'], GL: ['NA'], GM: ['AF'], GN: ['AF'],
	GP: ['NA'], GQ: ['AF'], GR: ['EU'], GT: ['NA'], GU: ['OC'],
	GW: ['AF'], GY: ['SA'],
	HK: ['AS'], HN: ['NA'], HR: ['EU'], HT: ['NA'], HU: ['EU'],
	ID: ['AS'], IE: ['EU'], IL: ['AS'], IN: ['AS'], IO: ['AF'],
	IQ: ['AS'], IR: ['AS'], IS: ['EU'], IT: ['EU'],
	JM: ['NA'], JO: ['AS'], JP: ['AS'],
	KE: ['AF'], KG: ['AS'], KH: ['AS'], KI: ['OC'], KM: ['AF'],
	KN: ['NA'], KP: ['AS'], KR: ['AS'], KW: ['AS'], KY: ['NA'],
	KZ: ['AS'],
	LA: ['AS'], LB: ['AS'], LC: ['NA'], LI: ['EU'], LK: ['AS'],
	LR: ['AF'], LS: ['AF'], LT: ['EU'], LU: ['EU'], LV: ['EU'],
	LY: ['AF'],
	MA: ['AF'], MC: ['EU'], MD: ['EU'], ME: ['EU'], MF: ['NA'],
	MG: ['AF'], MH: ['OC'], MK: ['EU'], ML: ['AF'], MM: ['AS'],
	MN: ['AS'], MO: ['AS'], MP: ['OC'], MQ: ['NA'], MR: ['AF'],
	MS: ['NA'], MT: ['EU'], MU: ['AF'], MV: ['AS'], MW: ['AF'],
	MX: ['NA'], MY: ['AS'], MZ: ['AF'],
	NA: ['AF'], NC: ['OC'], NE: ['AF'], NF: ['OC'], NG: ['AF'],
	NI: ['NA'], NL: ['EU'], NO: ['EU'], NP: ['AS'], NR: ['OC'],
	NU: ['OC'], NZ: ['OC'],
	OM: ['AS'],
	PA: ['NA'], PE: ['SA'], PF: ['OC'], PG: ['OC'], PH: ['AS'],
	PK: ['AS'], PL: ['EU'], PM: ['NA'], PR: ['NA'], PS: ['AS'],
	PT: ['EU'], PW: ['OC'], PY: ['SA'],
	QA: ['AS'],
	RE: ['AF'], RO: ['EU'], RS: ['EU'], RU: ['EU'], RW: ['AF'],
	SA: ['AS'], SB: ['OC'], SC: ['AF'], SD: ['AF'], SE: ['EU'],
	SG: ['AS'], SH: ['AF'], SI: ['EU'], SK: ['EU'], SL: ['AF'],
	SM: ['EU'], SN: ['AF'], SO: ['AF'], SR: ['SA'], ST: ['AF'],
	SV: ['NA'], SY: ['AS'], SZ: ['AF'],
	TC: ['NA'], TG: ['AF'], TH: ['AS'], TJ: ['AS'], TK: ['OC'],
	TL: ['AS'], TM: ['AS'], TN: ['AF'], TO: ['OC'], TR: ['AS'],
	TT: ['SA'], TV: ['OC'], TW: ['AS'], TZ: ['AF'],
	UA: ['EU'], UG: ['AF'], US: ['NA'], UY: ['SA'], UZ: ['AS'],
	VA: ['EU'], VC: ['NA'], VE: ['SA'], VG: ['NA'], VI: ['NA'],
	VN: ['AS'], VU: ['OC'],
	WF: ['OC'], WS: ['OC'],
	XK: ['EU'],
	YE: ['AS'], YT: ['AF'],
	ZA: ['AF'], ZM: ['AF'], ZW: ['AF'],
};

const REGION_FILTER: Record<CountryRegion, string[]> = {
	world: [],
	europe: ['EU'],
	dach: ['DACH'],
	asia: ['AS'],
	africa: ['AF'],
	oceania: ['OC'],
	'north-america': ['NA'],
	'south-america': ['SA'],
	americas: ['NA', 'SA'],
};

export type CountryOption = {
	value: string;
	label: string;
};

export const getCountryOptions = (
	region: CountryRegion,
	locale: string,
): CountryOption[] => {
	const displayNames = new Intl.DisplayNames([locale, 'en'], { type: 'region' });
	const filterRegions = REGION_FILTER[region];

	const codes =
		filterRegions.length === 0
			? Object.keys(COUNTRY_REGIONS)
			: Object.entries(COUNTRY_REGIONS)
					.filter(([, regions]) => regions.some((item) => filterRegions.includes(item)))
					.map(([code]) => code);

	return codes
		.map((code) => ({ value: code, label: displayNames.of(code) ?? code }))
		.sort((a, b) => a.label.localeCompare(b.label, locale));
};
