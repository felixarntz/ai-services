/**
 * WordPress dependencies
 */
import { PanelBody, TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import './style.scss';

/**
 * Renders the playground sidebar panel for AI model configuration.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function PlaygroundModelConfigPanel() {
	const [ panelOpened, setPanelOpened ] = useState( false );

	const { maxOutputTokens, temperature, topP } = useSelect( ( select ) => {
		const { getModelParam } = select( playgroundStore );

		return {
			maxOutputTokens: getModelParam( 'maxOutputTokens' ),
			temperature: getModelParam( 'temperature' ),
			topP: getModelParam( 'topP' ),
		};
	} );

	const { setModelParam } = useDispatch( playgroundStore );

	return (
		<PanelBody
			title={ __( 'Model configuration', 'ai-services' ) }
			opened={ panelOpened }
			onToggle={ () => setPanelOpened( ! panelOpened ) }
			className="ai-services-playground-model-config-panel"
		>
			<TextControl
				type="number"
				min="0"
				step="1"
				label={ __( 'Max output tokens', 'ai-services' ) }
				help={ __(
					'The maximum number of tokens to include in a response candidate.',
					'ai-services'
				) }
				value={ maxOutputTokens }
				onChange={ ( value ) =>
					setModelParam( 'maxOutputTokens', value )
				}
				__nextHasNoMarginBottom
			/>
			<TextControl
				type="number"
				min="0"
				max="1"
				step="0.01"
				label={ __( 'Temperature', 'ai-services' ) }
				help={ sprintf(
					/* translators: 1: Minimum value, 2: Maximum value */
					__(
						'Floating point value to control the randomness of the output, between %1$s and %2$s.',
						'ai-services'
					),
					'0.0',
					'1.0'
				) }
				value={ temperature }
				onChange={ ( value ) => setModelParam( 'temperature', value ) }
				__nextHasNoMarginBottom
			/>
			<TextControl
				type="number"
				min="0"
				step="0.01"
				label={ __( 'Top P', 'ai-services' ) }
				help={ __(
					'The maximum cumulative probability of tokens to consider when sampling.',
					'ai-services'
				) }
				value={ topP }
				onChange={ ( value ) => setModelParam( 'topP', value ) }
				__nextHasNoMarginBottom
			/>
		</PanelBody>
	);
}
