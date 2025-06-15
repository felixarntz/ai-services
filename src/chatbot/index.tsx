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
 * @since 0.1.0
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
	const renderTarget = document.getElementById( 'ai-services-chatbot-root' );
	if ( ! renderTarget ) {
		return;
	}

	mountApp( <ChatbotApp />, renderTarget );
} );
