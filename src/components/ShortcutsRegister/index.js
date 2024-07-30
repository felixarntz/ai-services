/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

export default function ShortcutsRegister() {
	// Registering the shortcuts.
	const { registerShortcut } = useDispatch( keyboardShortcutsStore );
	useEffect( () => {
		registerShortcut( {
			name: 'wp-oop-plugin-lib-example/settings-screen/next-region',
			category: 'global',
			description: __(
				'Navigate to the next part of the settings screen.',
				'wp-oop-plugin-lib-example'
			),
			keyCombination: {
				modifier: 'ctrl',
				character: '`',
			},
			aliases: [
				{
					modifier: 'access',
					character: 'n',
				},
			],
		} );

		registerShortcut( {
			name: 'wp-oop-plugin-lib-example/settings-screen/previous-region',
			category: 'global',
			description: __(
				'Navigate to the previous part of the settings screen.',
				'wp-oop-plugin-lib-example'
			),
			keyCombination: {
				modifier: 'ctrlShift',
				character: '`',
			},
			aliases: [
				{
					modifier: 'access',
					character: 'p',
				},
				{
					modifier: 'ctrlShift',
					character: '~',
				},
			],
		} );
	}, [ registerShortcut ] );

	return null;
}
