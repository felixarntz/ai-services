/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { preformatted } from '@wordpress/icons';

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

	return (
		<Button
			icon={ preformatted }
			label={ __( 'Toggle system instruction', 'ai-services' ) }
			className="is-compact"
			onClick={ toggleSystemInstruction }
			aria-controls="ai-services-playground-system-instruction"
			aria-pressed={ isSystemInstructionVisible }
			aria-expanded={ isSystemInstructionVisible }
		/>
	);
}
