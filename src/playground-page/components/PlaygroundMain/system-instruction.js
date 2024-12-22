/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

/**
 * Renders the system instruction UI.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function SystemInstruction() {
	const { systemInstruction, isSystemInstructionVisible } = useSelect(
		( select ) => {
			return {
				systemInstruction:
					select( playgroundStore ).getSystemInstruction(),
				isSystemInstructionVisible:
					select( playgroundStore ).isSystemInstructionVisible(),
			};
		}
	);

	const { setSystemInstruction } = useDispatch( playgroundStore );

	if ( ! isSystemInstructionVisible ) {
		return null;
	}

	return (
		<div
			id="ai-services-playground-system-instruction"
			className="ai-services-playground__system-instruction-container"
		>
			<TextareaControl
				className="ai-services-playground__system-instruction"
				label={ __( 'System instruction', 'ai-services' ) }
				placeholder={ __(
					'Enter AI system instruction',
					'ai-services'
				) }
				value={ systemInstruction }
				onChange={ ( value ) => setSystemInstruction( value ) }
				rows="4"
				__nextHasNoMarginBottom
			/>
		</div>
	);
}
