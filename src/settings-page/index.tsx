/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import SettingsApp from './components/SettingsApp';

/**
 * Mounts the given JSX into the DOM.
 *
 * @since n.e.x.t
 *
 * @param jsx          - The JSX node to be mounted.
 * @param renderTarget - The target element to render the JSX into.
 */
function mountApp( jsx: JSX.Element, renderTarget: Element ) {
	if ( createRoot ) {
		const root = createRoot( renderTarget );
		root.render( jsx );
	} else {
		render( jsx, renderTarget );
	}
}

// Initialize the app once the DOM is ready.
domReady( () => {
	const renderTarget = document.getElementById( 'settings-page-root' );
	if ( ! renderTarget ) {
		return;
	}

	mountApp( <SettingsApp />, renderTarget );
} );
