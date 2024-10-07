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
 * Mounts the given component into the DOM.
 *
 * @since 0.1.0
 *
 * @param {Component} Component    The component to be mounted.
 * @param {Element}   renderTarget The target element to render the component into.
 */
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

	mountApp( <SettingsApp />, renderTarget );
} );
