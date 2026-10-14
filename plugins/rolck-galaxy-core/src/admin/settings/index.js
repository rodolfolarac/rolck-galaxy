/**
 * Tela Rolck Galaxy → Configurações (React + componentes nativos do WordPress).
 */
import { createRoot } from '@wordpress/element';
import App from './App';
import './settings.scss';

const root = document.getElementById( 'rolck-settings-root' );
if ( root ) {
	createRoot( root ).render( <App /> );
}
