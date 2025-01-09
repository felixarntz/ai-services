/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import PlaygroundApp from './components/PlaygroundApp';
import './store';

/**
 * Mounts the given component into the DOM.
 *
 * @since 0.4.0
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
	const renderTarget = document.getElementById( 'playground-page-root' );
	if ( ! renderTarget ) {
		return;
	}

	mountApp( <PlaygroundApp />, renderTarget );
} );
