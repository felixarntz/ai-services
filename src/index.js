/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import App from './components/App';
import './store';
import './style.scss';

function mountApp( Component, renderTarget ) {
	if ( createRoot ) {
		const root = createRoot( renderTarget );
		root.render( Component );
	} else {
		render( Component, renderTarget );
	}
}

// Initialize the app once the DOM is ready.
domReady( () => {
	const renderTarget = document.getElementById( 'settings-page-root' );
	if ( ! renderTarget ) {
		return;
	}

	mountApp( <App />, renderTarget );
} );
