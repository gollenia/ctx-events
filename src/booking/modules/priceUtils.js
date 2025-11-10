const formatCurrency = ( price, locale, currency ) => {
	return new Intl.NumberFormat( locale, {
		style: 'currency',
		currency,
	} ).format( price );
};

export { formatCurrency };
