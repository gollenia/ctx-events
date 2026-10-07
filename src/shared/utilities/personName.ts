export type PersonNameMeta = {
	_person_prefix?: string;
	_person_first_name?: string;
	_person_last_name?: string;
	_person_suffix?: string;
};

export const formatPersonName = (person: PersonNameMeta): string =>
	[
		person._person_prefix,
		person._person_first_name,
		person._person_last_name,
		person._person_suffix,
	]
		.map((part) => part?.trim())
		.filter((part): part is string => Boolean(part))
		.join(' ');
