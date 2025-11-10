import ReactDOM from 'react-dom';
import './style.scss';

import GatewayTable from './GatewayTable';

function GatewayAdmin() {
	document.addEventListener( 'DOMContentLoaded', () => {
		const rootElement = document.getElementById( 'gateway-admin' );
		if ( ! rootElement ) return;

		const app = ReactDOM.createRoot( rootElement );

		app.render( <GatewayTable /> );
	} );
}
export { GatewayAdmin };
