/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

/**
 * Renders a utility component to register general keyboard shortcuts for the application.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function ShortcutsRegister() {
	// Registering the shortcuts.
	const { registerShortcut } = useDispatch( keyboardShortcutsStore );
	useEffect( () => {
		registerShortcut( {
			name: 'wp-oop-plugin-lib-example/keyboard-shortcuts',
			category: 'main',
			description: __(
				'Display these keyboard shortcuts.',
				'wp-oop-plugin-lib-example'
			),
			keyCombination: {
				modifier: 'access',
				character: 'h',
			},
		} );

		registerShortcut( {
			name: 'wp-oop-plugin-lib-example/next-region',
			category: 'global',
			description: __(
				'Navigate to the next part of the screen.',
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
			name: 'wp-oop-plugin-lib-example/previous-region',
			category: 'global',
			description: __(
				'Navigate to the previous part of the screen.',
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

		registerShortcut( {
			name: 'wp-oop-plugin-lib-example/toggle-distraction-free',
			category: 'global',
			description: __(
				'Toggle distraction free mode.',
				'wp-oop-plugin-lib-example'
			),
			keyCombination: {
				modifier: 'primaryShift',
				character: '\\',
			},
		} );
	}, [ registerShortcut ] );

	return null;
}
