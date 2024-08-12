/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PreferenceToggleMenuItem } from '@wordpress/preferences';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

export default function DistractionFreePreferenceToggleMenuItem() {
	const shortcut = useSelect( ( select ) =>
		select( keyboardShortcutsStore ).getShortcutRepresentation(
			'wp-oop-plugin-lib-example/toggle-distraction-free',
			'display'
		)
	);

	return (
		<PreferenceToggleMenuItem
			scope="wp-oop-plugin-lib-example"
			name="distractionFree"
			label={ __( 'Distraction free', 'wp-oop-plugin-lib-example' ) }
			info={ __(
				'Hide secondary interface to help focus',
				'wp-oop-plugin-lib-example'
			) }
			messageActivated={ __(
				'Distraction free mode activated',
				'wp-oop-plugin-lib-example'
			) }
			messageDeactivated={ __(
				'Distraction free mode deactivated',
				'wp-oop-plugin-lib-example'
			) }
			shortcut={ shortcut }
		/>
	);
}
