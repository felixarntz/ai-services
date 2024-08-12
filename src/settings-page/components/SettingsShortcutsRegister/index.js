/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

export default function SettingsShortcutsRegister() {
	// Registering the shortcuts.
	const { registerShortcut } = useDispatch( keyboardShortcutsStore );
	useEffect( () => {
		registerShortcut( {
			name: 'wp-oop-plugin-lib-example/save',
			category: 'global',
			description: __(
				'Save your changes.',
				'wp-oop-plugin-lib-example'
			),
			keyCombination: {
				modifier: 'primary',
				character: 's',
			},
		} );
	}, [ registerShortcut ] );

	return null;
}
