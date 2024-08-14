/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { MenuItem } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as interfaceStore } from '@wordpress/interface';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

/**
 * Renders a menu item to open the keyboard shortcuts help modal.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function KeyboardShortcutsMenuItem() {
	const { openModal } = useDispatch( interfaceStore );
	const shortcut = useSelect( ( select ) =>
		select( keyboardShortcutsStore ).getShortcutRepresentation(
			'wp-starter-plugin/keyboard-shortcuts',
			'display'
		)
	);

	return (
		<MenuItem
			onClick={ () =>
				openModal( 'wp-starter-plugin/keyboard-shortcuts-help' )
			}
			shortcut={ shortcut }
		>
			{ __( 'Keyboard shortcuts', 'wp-starter-plugin' ) }
		</MenuItem>
	);
}
