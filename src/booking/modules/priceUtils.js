/*
const fullPrice = ( coupon, tickets ) => {
	let sum = 0;

	for ( let ticket in tickets ) {
		sum += ticketPrice( ticket );
	}

	if ( ! coupon.success ) return sum;
	return coupon.percent ? sum - parseInt( coupon.discount ) : sum - ( sum / 100 ) * parseInt( coupon.discount );
};
*/
const formatCurrency = ( price, locale, currency ) => {
	return new Intl.NumberFormat( locale, {
		style: 'currency',
		currency,
	} ).format( price );
};

const ticketPrice = ( key, appState ) => {
	return (
		eventData.tickets_available[ key ].price *
		appState.request.tickets.reduce( ( n, ticket ) => {
			return n + ( ticket.id == eventData.tickets_available[ key ].id );
		}, 0 )
	);
};

export { formatCurrency, ticketPrice };
