export type SetBlockAttributes<TAttributes> = (
	attributes: Partial<TAttributes>,
) => void;

export type DetailBlockContext = {
	postType?: string;
	postId?: number;
};

export type DetailBlockProps<TAttributes> = {
	attributes: TAttributes;
	setAttributes: SetBlockAttributes<TAttributes>;
	context?: DetailBlockContext;
	className?: string;
	clientId?: string;
};

export type EventDateMeta = {
	_event_start?: string;
	_event_end?: string;
};

export type EventAudienceMeta = {
	_event_audience?: string;
};

export type EventLocationMeta = {
	_location_id?: number;
};

export type EventSpeakerMeta = {
	_speaker_id?: number;
};

export type EventRsvpMeta = {
	_event_rsvp_start?: string;
	_event_rsvp_end?: string;
};

export type EventTicketsMeta = {
	_event_tickets?: Array<{
		ticket_price?: number | string;
	}>;
};

export type LocationRecord = {
	title?: {
		rendered?: string;
	};
	meta?: {
		_location_address?: string;
		_location_postcode?: string;
		_location_town?: string;
		_location_country?: string;
	};
	_embedded?: {
		'wp:featuredmedia'?: Array<{
			media_details?: {
				sizes?: {
					thumbnail?: {
						source_url?: string;
					};
				};
			};
		}>;
	};
};

export type SpeakerRecord = {
	id: number;
	link?: string;
	gender?: string;
	title?: {
		rendered?: string;
		raw?: string;
	};
	meta?: {
		_email?: string;
		_phone?: string;
	};
	_embedded?: {
		'wp:featuredmedia'?: Array<{
			source_url?: string;
			media_details?: {
				sizes?: {
					thumbnail?: {
						source_url?: string;
					};
				};
			};
		}>;
	};
};

export type SpacesRecord = {
	extras?: {
		spaces?: number;
	};
};

export type ColorValue = {
	color?: string;
};

export type DetailsItemAttributes = {
	icon: string;
	url: string;
	urlIcon: string;
	imageUrl: string;
	imageId: number;
	customIconColor: string;
	customIconBackgroundColor: string;
	textAlign?: string;
	opensInNewTab?: boolean;
	rel?: string;
};

export type DetailsDateAttributes = {
	description: string;
	iCalLink: boolean;
};

export type DetailsAudienceAttributes = {
	description: string;
};

export type DetailsLocationAttributes = {
	description: string;
	showAddress: boolean;
	showZip: boolean;
	showCity: boolean;
	showCountry: boolean;
	showTitle: boolean;
	showLink: boolean;
	url: string;
};

export type DetailsPersonAttributes = {
	showPortrait: boolean;
	description: string;
	showLink: boolean;
	customSpeakerId: number;
	url: string;
	linkTo: string;
};

export type DetailsPriceAttributes = {
	description: string;
	overwritePrice: string;
};

export type DetailsShutdownAttributes = {
	format: string;
	description: string;
};

export type DetailsSpacesAttributes = {
	description: string;
	showNumber: boolean;
	warningText: string;
	warningThreshold: number;
	okText: string;
	bookedUpText: string;
};

export type DetailsTimeAttributes = {
	description: string;
};

export type SpeakerOption = {
	label: string;
	value: number;
	media?: string;
};
