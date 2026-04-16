const countries = [
	{
		code: 'AF',
		regions: ['AS'],
	},
	{
		code: 'AL',
		regions: ['EU'],
	},
	{
		code: 'DZ',
		regions: ['AF'],
	},
	{
		code: 'AS',
		regions: ['OC'],
	},
	{
		code: 'AD',
		regions: ['EU'],
	},
	{
		code: 'AO',
		regions: ['AF'],
	},
	{
		code: 'AI',
		regions: ['NA'],
	},
	{
		code: 'AG',
		regions: ['NA'],
	},
	{
		code: 'AR',
		regions: ['SA'],
	},
	{
		code: 'AM',
		regions: ['AS'],
	},
	{
		code: 'AT',
		regions: ['EU', 'DACH'],
	},
	{
		code: 'AW',
		regions: ['SA'],
	},
	{
		code: 'AU',
		regions: ['OC'],
	},
	{
		code: 'AZ',
		regions: ['AS'],
	},
	{
		code: 'BS',
		regions: ['NA'],
	},
	{
		code: 'BH',
		regions: ['AS'],
	},
	{
		code: 'BD',
		regions: ['AS'],
	},
	{
		code: 'BB',
		regions: ['NA'],
	},
	{
		code: 'BY',
		regions: ['EU'],
	},
	{
		code: 'BE',
		regions: ['EU'],
	},
	{
		code: 'BZ',
		regions: ['NA'],
	},
	{
		code: 'BJ',
		regions: ['AF'],
	},
	{
		code: 'BM',
		regions: ['NA'],
	},
	{
		code: 'BT',
		regions: ['AS'],
	},
	{
		code: 'BO',
		regions: ['SA'],
	},
	{
		code: 'BA',
		regions: ['EU'],
	},
	{
		code: 'BW',
		regions: ['AF'],
	},
	{
		code: 'BR',
		regions: ['SA'],
	},
	{
		code: 'IO',
		regions: ['AF'],
	},
	{
		code: 'VG',
		regions: ['NA'],
	},
	{
		code: 'VI',
		regions: ['NA'],
	},
	{
		code: 'BN',
		regions: ['AS'],
	},
	{
		code: 'BG',
		regions: ['EU'],
	},
	{
		code: 'BF',
		regions: ['AF'],
	},
	{
		code: 'BI',
		regions: ['AF'],
	},
	{
		code: 'KH',
		regions: ['AS'],
	},
	{
		code: 'CM',
		regions: ['AF'],
	},
	{
		code: 'CA',
		regions: ['NA'],
	},
	{
		code: 'CV',
		regions: ['AF'],
	},
	{
		code: 'KY',
		regions: ['NA'],
	},
	{
		code: 'CF',
		regions: ['AF'],
	},
	{
		code: 'CF',
		regions: ['AF'],
	},
	{
		code: 'CL',
		regions: ['SA'],
	},
	{
		code: 'CN',
		regions: ['AS'],
	},
	{
		code: 'CO',
		regions: ['SA'],
	},
	{
		code: 'KM',
		regions: ['AF'],
	},
	{
		code: 'CG',
		regions: ['AF'],
	},
	{
		code: 'CD',
		regions: ['AF'],
	},
	{
		code: 'CK',
		regions: ['OC'],
	},
	{
		code: 'CR',
		regions: ['NA'],
	},
	{
		code: 'HR',
		regions: ['EU'],
	},
	{
		code: 'CU',
		regions: ['NA'],
	},
	{
		code: 'CU',
		regions: ['NA'],
	},
	{
		code: 'CY',
		regions: ['EU'],
	},
	{
		code: 'CZ',
		regions: ['EU'],
	},
	{
		code: 'DK',
		regions: ['EU'],
	},
	{
		code: 'DJ',
		regions: ['AF'],
	},
	{
		code: 'DM',
		regions: ['NA'],
	},
	{
		code: 'DO',
		regions: ['NA'],
	},
	{
		code: 'EC',
		regions: ['SA'],
	},
	{
		code: 'EG',
		regions: ['AF'],
	},
	{
		code: 'SV',
		regions: ['NA'],
	},
	{
		code: 'GQ',
		regions: ['AF'],
	},
	{
		code: 'ER',
		regions: ['AF'],
	},
	{
		code: 'EE',
		regions: ['EU'],
	},
	{
		code: 'ET',
		regions: ['AF'],
	},
	{
		code: 'FK',
		regions: ['SA'],
	},
	{
		code: 'FO',
		regions: ['EU'],
	},
	{
		code: 'FJ',
		regions: ['OC'],
	},
	{
		code: 'FI',
		regions: ['EU'],
	},
	{
		code: 'FR',
		regions: ['EU'],
	},
	{
		code: 'GF',
		regions: ['SA'],
	},
	{
		code: 'PF',
		regions: ['OC'],
	},
	{
		code: 'GA',
		regions: ['AF'],
	},
	{
		code: 'GM',
		regions: ['AF'],
	},
	{
		code: 'GE',
		regions: ['AS'],
	},
	{
		code: 'DE',
		regions: ['EU', 'DACH'],
	},
	{
		code: 'GH',
		regions: ['AF'],
	},
	{
		code: 'GI',
		regions: ['EU'],
	},
	{
		code: 'GR',
		regions: ['EU'],
	},
	{
		code: 'GL',
		regions: ['NA'],
	},
	{
		code: 'GD',
		regions: ['NA'],
	},
	{
		code: 'GP',
		regions: ['NA'],
	},
	{
		code: 'GU',
		regions: ['OC'],
	},
	{
		code: 'GT',
		regions: ['NA'],
	},
	{
		code: 'GN',
		regions: ['AF'],
	},
	{
		code: 'GW',
		regions: ['AF'],
	},
	{
		code: 'GY',
		regions: ['SA'],
	},
	{
		code: 'HT',
		regions: ['Americas'],
	},
	{
		code: 'VA',
		regions: ['EU'],
	},
	{
		code: 'HN',
		regions: ['NA'],
	},
	{
		code: 'HK',
		regions: ['AS'],
	},
	{
		code: 'HU',
		regions: ['EU'],
	},
	{
		code: 'IS',
		regions: ['EU'],
	},
	{
		code: 'IN',
		regions: ['AS'],
	},
	{
		code: 'ID',
		regions: ['AS'],
	},
	{
		code: 'CI',
		regions: ['AF'],
	},
	{
		code: 'IR',
		regions: ['AS'],
	},
	{
		code: 'IQ',
		regions: ['AS'],
	},
	{
		code: 'IE',
		regions: ['EU'],
	},
	{
		code: 'IL',
		regions: ['AS'],
	},
	{
		code: 'IT',
		regions: ['EU'],
	},
	{
		code: 'JM',
		regions: ['NA'],
	},
	{
		code: 'JP',
		regions: ['AS'],
	},
	{
		code: 'JO',
		regions: ['AS'],
	},
	{
		code: 'KZ',
		regions: ['AS'],
	},
	{
		code: 'KE',
		regions: ['AF'],
	},
	{
		code: 'KI',
		regions: ['OC'],
	},
	{
		code: 'KW',
		regions: ['AS'],
	},
	{
		code: 'KG',
		regions: ['AS'],
	},
	{
		code: 'LA',
		regions: ['AS'],
	},
	{
		code: 'LV',
		regions: ['EU'],
	},
	{
		code: 'LB',
		regions: ['AS'],
	},
	{
		code: 'LS',
		regions: ['AF'],
	},
	{
		code: 'LR',
		regions: ['AF'],
	},
	{
		code: 'LY',
		regions: ['AF'],
	},
	{
		code: 'LI',
		regions: ['EU'],
	},
	{
		code: 'LT',
		regions: ['EU'],
	},
	{
		code: 'LU',
		regions: ['EU'],
	},
	{
		code: 'MO',
		regions: ['AS'],
	},
	{
		code: 'MK',
		regions: ['EU'],
	},
	{
		code: 'MG',
		regions: ['AF'],
	},
	{
		code: 'MW',
		regions: ['AF'],
	},
	{
		code: 'MY',
		regions: ['AS'],
	},
	{
		code: 'MV',
		regions: ['AS'],
	},
	{
		code: 'ML',
		regions: ['AF'],
	},
	{
		code: 'MT',
		regions: ['EU'],
	},
	{
		code: 'MH',
		regions: ['OC'],
	},
	{
		code: 'MQ',
		regions: ['Americas'],
	},
	{
		code: 'MR',
		regions: ['AF'],
	},
	{
		code: 'MU',
		regions: ['AF'],
	},
	{
		code: 'YT',
		regions: ['AF'],
	},
	{
		code: 'MX',
		regions: ['NA'],
	},
	{
		code: 'FM',
		regions: ['OC'],
	},
	{
		code: 'MD',
		regions: ['EU'],
	},
	{
		code: 'MC',
		regions: ['EU'],
	},
	{
		code: 'MN',
		regions: ['AS'],
	},
	{
		code: 'ME',
		regions: ['EU'],
	},
	{
		code: 'MS',
		regions: ['NA'],
	},
	{
		code: 'MA',
		regions: ['AF'],
	},
	{
		code: 'MZ',
		regions: ['AF'],
	},
	{
		code: 'MM',
		regions: ['AS'],
	},
	{
		code: 'NA',
		regions: ['AF'],
	},
	{
		code: 'NR',
		regions: ['OC'],
	},
	{
		code: 'NP',
		regions: ['AS'],
	},
	{
		code: 'NL',
		regions: ['EU'],
	},
	{
		code: 'NC',
		regions: ['OC'],
	},
	{
		code: 'NZ',
		regions: ['OC'],
	},
	{
		code: 'NI',
		regions: ['NA'],
	},
	{
		code: 'NE',
		regions: ['AF'],
	},
	{
		code: 'NG',
		regions: ['AF'],
	},
	{
		code: 'NU',
		regions: ['OC'],
	},
	{
		code: 'NF',
		regions: ['OC'],
	},
	{
		code: 'KP',
		regions: ['AS'],
	},
	{
		code: 'MP',
		regions: ['OC'],
	},
	{
		code: 'NO',
		regions: ['EU'],
	},
	{
		code: 'OM',
		regions: ['AS'],
	},
	{
		code: 'PK',
		regions: ['AS'],
	},
	{
		code: 'PW',
		regions: ['OC'],
	},
	{
		code: 'PS',
		regions: ['AS'],
	},
	{
		code: 'PA',
		regions: ['NA'],
	},
	{
		code: 'PG',
		regions: ['OC'],
	},
	{
		code: 'PY',
		regions: ['SA'],
	},
	{
		code: 'PE',
		regions: ['SA'],
	},
	{
		code: 'PH',
		regions: ['AS'],
	},
	{
		code: 'PL',
		regions: ['EU'],
	},
	{
		code: 'PT',
		regions: ['EU'],
	},
	{
		code: 'PR',
		regions: ['NA'],
	},
	{
		code: 'QA',
		regions: ['AS'],
	},
	{
		code: 'XK',
		regions: ['EU'],
	},
	{
		code: 'RE',
		regions: ['AF'],
	},
	{
		code: 'RO',
		regions: ['EU'],
	},
	{
		code: 'RU',
		regions: ['EU'],
	},
	{
		code: 'RW',
		regions: ['AF'],
	},
	{
		code: 'BL',
		regions: ['NA'],
	},
	{
		code: 'SH',
		regions: ['AF'],
	},
	{
		code: 'KN',
		regions: ['NA'],
	},
	{
		code: 'LC',
		regions: ['NA'],
	},
	{
		code: 'MF',
		regions: ['NA'],
	},
	{
		code: 'PM',
		regions: ['NA'],
	},
	{
		code: 'VC',
		regions: ['NA'],
	},
	{
		code: 'WS',
		regions: ['OC'],
	},
	{
		code: 'SM',
		regions: ['EU'],
	},
	{
		code: 'ST',
		regions: ['AF'],
	},
	{
		code: 'SA',
		regions: ['AS'],
	},
	{
		code: 'SN',
		regions: ['AF'],
	},
	{
		code: 'RS',
		regions: ['EU'],
	},
	{
		code: 'SC',
		regions: ['AF'],
	},
	{
		code: 'SL',
		regions: ['AF'],
	},
	{
		code: 'SG',
		regions: ['AS'],
	},
	{
		code: 'SG',
		regions: ['AS'],
	},
	{
		code: 'SK',
		regions: ['EU'],
	},
	{
		code: 'SI',
		regions: ['EU'],
	},
	{
		code: 'SB',
		regions: ['OC'],
	},
	{
		code: 'SO',
		regions: ['AF'],
	},
	{
		code: 'ZA',
		regions: ['AF'],
	},
	{
		code: 'KR',
		regions: ['AS'],
	},
	{
		code: 'ES',
		regions: ['EU'],
	},
	{
		code: 'LK',
		regions: ['AS'],
	},
	{
		code: 'SD',
		regions: ['AF'],
	},
	{
		code: 'SR',
		regions: ['SA'],
	},
	{
		code: 'SZ',
		regions: ['AF'],
	},
	{
		code: 'SE',
		regions: ['EU'],
	},
	{
		code: 'CH',
		regions: ['EU', 'DACH'],
	},
	{
		code: 'SY',
		regions: ['AS'],
	},
	{
		code: 'TW',
		regions: ['AS'],
	},
	{
		code: 'TJ',
		regions: ['AS'],
	},
	{
		code: 'TZ',
		regions: ['AF'],
	},
	{
		code: 'TH',
		regions: ['AS'],
	},
	{
		code: 'TL',
		regions: ['AS'],
	},
	{
		code: 'TG',
		regions: ['AF'],
	},
	{
		code: 'TK',
		regions: ['OC'],
	},
	{
		code: 'TO',
		regions: ['OC'],
	},
	{
		code: 'TT',
		regions: ['SA'],
	},
	{
		code: 'TN',
		regions: ['AF'],
	},
	{
		code: 'TR',
		regions: ['AS'],
	},
	{
		code: 'TM',
		regions: ['AS'],
	},
	{
		code: 'TC',
		regions: ['NA'],
	},
	{
		code: 'TV',
		regions: ['OC'],
	},
	{
		code: 'UG',
		regions: ['AF'],
	},
	{
		code: 'UA',
		regions: ['EU'],
	},
	{
		code: 'AE',
		regions: ['AS'],
	},
	{
		code: 'GB',
		regions: ['EU'],
	},
	{
		code: 'US',
		regions: ['NA'],
	},
	{
		code: 'UY',
		regions: ['SA'],
	},
	{
		code: 'UZ',
		regions: ['AS'],
	},
	{
		code: 'VU',
		regions: ['OC'],
	},
	{
		code: 'VE',
		regions: ['SA'],
	},
	{
		code: 'VN',
		regions: ['AS'],
	},
	{
		code: 'WF',
		regions: ['OC'],
	},
	{
		code: 'WF',
		regions: ['OC'],
	},
	{
		code: 'YE',
		regions: ['AS'],
	},
	{
		code: 'ZM',
		regions: ['AF'],
	},
	{
		code: 'ZW',
		regions: ['AF'],
	},
];

const allCountryCodes = countries.map((c) => c.code);

const countriesByRegionMap = countries.reduce(
	(acc, country) => {
		country.regions.forEach((region) => {
			if (!acc[region]) acc[region] = [];
			acc[region].push(country.code);
		});
		return acc;
	},
	{} as Record<string, string[]>,
);

export const getCountries = (): string[] => allCountryCodes;

export const getCountriesByRegion = (region: string): string[] => {
	return countriesByRegionMap[region] || [];
};
