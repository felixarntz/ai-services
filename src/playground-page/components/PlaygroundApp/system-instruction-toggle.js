/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { preformatted } from '@wordpress/icons';
import {
	useShortcut,
	store as keyboardShortcutsStore,
} from '@wordpress/keyboard-shortcuts';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

/**
 * Renders the system instruction toggle button.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function SystemInstructionToggle() {
	const { registerShortcut } = useDispatch( keyboardShortcutsStore );
	useEffect( () => {
		registerShortcut( {
			name: 'ai-services/toggle-system-instruction',
			category: 'global',
			description: __(
				'Show or hide the system instruction.',
				'ai-services'
			),
			keyCombination: {
				modifier: 'primaryShift',
				character: '.',
			},
		} );
	}, [ registerShortcut ] );

	const isSystemInstructionVisible = useSelect( ( select ) =>
		select( playgroundStore ).isSystemInstructionVisible()
	);

	const { showSystemInstruction, hideSystemInstruction } =
		useDispatch( playgroundStore );
	const toggleSystemInstruction = () => {
		if ( isSystemInstructionVisible ) {
			hideSystemInstruction();
		} else {
			showSystemInstruction();
		}
	};

	useShortcut( 'ai-services/toggle-system-instruction', () => {
		toggleSystemInstruction();
	} );

	const shortcut = useSelect( ( select ) =>
		select( keyboardShortcutsStore ).getShortcutRepresentation(
			'ai-services/toggle-system-instruction',
			'display'
		)
	);

	return (
		<Button
			icon={ preformatted }
			label={ __( 'Toggle system instruction', 'ai-services' ) }
			className="is-compact"
			onClick={ toggleSystemInstruction }
			aria-controls="ai-services-playground-system-instruction"
			aria-pressed={ isSystemInstructionVisible }
			aria-expanded={ isSystemInstructionVisible }
			shortcut={ shortcut }
		/>
	);
}
