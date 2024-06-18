/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

// Initialize the app once the DOM is ready.
domReady( () => {
	const renderTarget = document.getElementById( 'settings-page-root' );
	if ( ! renderTarget ) {
		return;
	}

	if ( createRoot ) {
		const root = createRoot( renderTarget );
		root.render(
			<div>
				<p>The JS app is loaded.</p>
			</div>
		);
	} else {
		render(
			<div>
				<p>The JS app is loaded.</p>
			</div>,
			renderTarget
		);
	}
} );
