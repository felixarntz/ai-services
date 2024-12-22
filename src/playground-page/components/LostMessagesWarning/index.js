/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

/**
 * Renders a utility component to conditionally trigger the browser warning about lost messages.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function LostMessagesWarning() {
	const messages = useSelect( ( select ) =>
		select( playgroundStore ).getMessages()
	);
	const isDirty = !! messages.length;

	useEffect( () => {
		const warnIfLostMessages = ( event ) => {
			if ( isDirty ) {
				event.returnValue = __(
					'Messages are not being saved. If you proceed, they will be lost.',
					'ai-services'
				);
				return event.returnValue;
			}
		};

		window.addEventListener( 'beforeunload', warnIfLostMessages );
		return () => {
			window.removeEventListener( 'beforeunload', warnIfLostMessages );
		};
	}, [ isDirty ] );

	return null;
}
