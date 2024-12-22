/**
 * External dependencies
 */
import { enums, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { send, upload } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

const EMPTY_ARRAY = [];

/**
 * Renders the prompt input UI.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function Input() {
	const [ prompt, setPrompt ] = useState( '' );

	const { service, model, capabilities } = useSelect( ( select ) => {
		const { getService, getModel } = select( playgroundStore );
		const { getServices } = select( aiStore );

		const currentService = getService();
		const currentModel = getModel();

		let currentCapabilities = EMPTY_ARRAY;
		if ( currentService && currentModel ) {
			const services = getServices();
			if (
				services &&
				services[ currentService ] &&
				services[ currentService ].available_models[ currentModel ]
			) {
				currentCapabilities =
					services[ currentService ].available_models[ currentModel ];
			}
		}

		return {
			service: currentService,
			model: currentModel,
			capabilities: currentCapabilities,
		};
	} );

	const { sendMessage } = useDispatch( playgroundStore );

	const disabled = ! service || ! model || ! prompt;

	const sendPrompt = async () => {
		if ( disabled ) {
			return;
		}

		await sendMessage( prompt );
		setPrompt( '' );
	};

	return (
		<div className="ai-services-playground__input-backdrop">
			<div className="ai-services-playground__input-container">
				<textarea
					className="ai-services-playground__input"
					placeholder={ __( 'Enter AI prompt', 'ai-services' ) }
					aria-label={ __( 'AI prompt', 'ai-services' ) }
					value={ prompt }
					onChange={ ( event ) => setPrompt( event.target.value ) }
					rows="2"
				></textarea>
				<div className="ai-services-playground__input-actions">
					<div className="ai-services-playground__input-action-group">
						{ capabilities.includes(
							enums.AiCapability.MULTIMODAL_INPUT
						) && (
							<button
								className="ai-services-playground__input-action ai-services-playground__input-action--secondary"
								aria-label={ __(
									'Upload media for multimodal prompt',
									'ai-services'
								) }
								disabled={ true }
								onClick={ () => {} }
							>
								{ upload }
							</button>
						) }
					</div>
					<div className="ai-services-playground__input-action-group">
						<button
							className="ai-services-playground__input-action ai-services-playground__input-action--primary"
							aria-label={ __( 'Send AI prompt', 'ai-services' ) }
							disabled={ disabled }
							onClick={ sendPrompt }
						>
							{ send }
						</button>
					</div>
				</div>
			</div>
		</div>
	);
}
