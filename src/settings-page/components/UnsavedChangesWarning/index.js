/**
 * External dependencies
 */
import { store as pluginStore } from '@wp-oop-plugin-lib-example/store';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default function UnsavedChangesWarning() {
	const isDirty = useSelect( ( select ) => {
		const { hasModifiedSettings } = select( pluginStore );

		return hasModifiedSettings();
	} );

	useEffect( () => {
		const warnIfUnsavedChanges = ( event ) => {
			if ( isDirty ) {
				event.returnValue = __(
					'You have unsaved changes. If you proceed, they will be lost.',
					'wp-oop-plugin-lib-example'
				);
				return event.returnValue;
			}
		};

		window.addEventListener( 'beforeunload', warnIfUnsavedChanges );
		return () => {
			window.removeEventListener( 'beforeunload', warnIfUnsavedChanges );
		};
	}, [ isDirty ] );

	return null;
}
