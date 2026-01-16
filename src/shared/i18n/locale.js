const getLocale = () => {
    return window.eventBlocksLocalization?.locale 
        || (navigator.languages ? navigator.languages[0] : navigator.language)
        || 'de-DE';
};