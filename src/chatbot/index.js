/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai-store';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import ChatbotApp from './components/ChatbotApp';

/**
 * Mounts the given component into the DOM.
 *
 * @since n.e.x.t
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
	const renderTarget = document.getElementById( 'ai-services-chatbot-root' );
	if ( ! renderTarget ) {
		return;
	}

	console.log( 'Chatbot loaded, using store:', aiStore.name ); // eslint-disable-line no-console

	mountApp( <ChatbotApp />, renderTarget );
} );
