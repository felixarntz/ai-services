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
			name: 'wp-starter-plugin/keyboard-shortcuts',
			category: 'main',
			description: __(
				'Display these keyboard shortcuts.',
				'wp-starter-plugin'
			),
			keyCombination: {
				modifier: 'access',
				character: 'h',
			},
		} );

		registerShortcut( {
			name: 'wp-starter-plugin/next-region',
			category: 'global',
			description: __(
				'Navigate to the next part of the screen.',
				'wp-starter-plugin'
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
			name: 'wp-starter-plugin/previous-region',
			category: 'global',
			description: __(
				'Navigate to the previous part of the screen.',
				'wp-starter-plugin'
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
			name: 'wp-starter-plugin/toggle-distraction-free',
			category: 'global',
			description: __(
				'Toggle distraction free mode.',
				'wp-starter-plugin'
			),
			keyCombination: {
				modifier: 'primaryShift',
				character: '\\',
			},
		} );
	}, [ registerShortcut ] );

	return null;
}
