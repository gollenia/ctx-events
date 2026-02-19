const getLocale = (): string => {
    return window.eventBlocksLocalization?.locale 
        || (navigator.languages ? navigator.languages[0] : navigator.language)
        || 'de-DE';
};

export default getLocale;