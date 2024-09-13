/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PreferenceToggleMenuItem } from '@wordpress/preferences';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

/**
 * Renders a menu item to toggle the distraction free mode for the application.
 *
 * By default, distraction free mode is disabled and can only be enabled via shortcut.
 * This component can be rendered in any menu to allow users to toggle the distraction free mode intuitively in the UI.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function DistractionFreePreferenceToggleMenuItem() {
	const shortcut = useSelect( ( select ) =>
		select( keyboardShortcutsStore ).getShortcutRepresentation(
			'ai-services/toggle-distraction-free',
			'display'
		)
	);

	return (
		<PreferenceToggleMenuItem
			scope="ai-services"
			name="distractionFree"
			label={ __( 'Distraction free', 'ai-services' ) }
			info={ __(
				'Hide secondary interface to help focus',
				'ai-services'
			) }
			messageActivated={ __(
				'Distraction free mode activated',
				'ai-services'
			) }
			messageDeactivated={ __(
				'Distraction free mode deactivated',
				'ai-services'
			) }
			shortcut={ shortcut }
		/>
	);
}
