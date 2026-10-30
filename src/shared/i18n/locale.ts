const getLocale = (): string => {
    const locale = window.eventBlocksLocalization?.locale
        || (navigator.languages ? navigator.languages[0] : navigator.language)
        || 'de-DE';

    return locale.replace('_', '-');
};

export default getLocale;
