/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

/**
 * Internal dependencies
 */
import { useHasSidebar } from '../Sidebar';

/**
 * Renders a utility component to register general keyboard shortcuts for the application.
 *
 * @since 0.1.0
 *
 * @returns The component to be rendered.
 */
export default function ShortcutsRegister() {
	const hasSidebar = useHasSidebar();

	// Registering the shortcuts.
	const { registerShortcut } = useDispatch( keyboardShortcutsStore );
	useEffect( () => {
		registerShortcut( {
			name: 'ai-services/keyboard-shortcuts',
			category: 'main',
			description: __(
				'Display these keyboard shortcuts.',
				'ai-services'
			),
			keyCombination: {
				modifier: 'access',
				character: 'h',
			},
		} );

		registerShortcut( {
			name: 'ai-services/next-region',
			category: 'global',
			description: __(
				'Navigate to the next part of the screen.',
				'ai-services'
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
			name: 'ai-services/previous-region',
			category: 'global',
			description: __(
				'Navigate to the previous part of the screen.',
				'ai-services'
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
			name: 'ai-services/toggle-distraction-free',
			category: 'global',
			description: __( 'Toggle distraction free mode.', 'ai-services' ),
			keyCombination: {
				modifier: 'primaryShift',
				character: '\\',
			},
		} );

		if ( hasSidebar ) {
			registerShortcut( {
				name: 'ai-services/toggle-sidebar',
				category: 'global',
				description: __( 'Show or hide the sidebar.', 'ai-services' ),
				keyCombination: {
					modifier: 'primaryShift',
					character: ',',
				},
			} );
		}
	}, [ registerShortcut, hasSidebar ] );

	return null;
}
