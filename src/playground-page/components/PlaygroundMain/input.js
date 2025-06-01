/**
 * External dependencies
 */
import { enums, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { Flex, Notice, ToggleControl } from '@wordpress/components';
import {
	useCallback,
	useEffect,
	useMemo,
	useState,
	useRef,
} from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { send, upload, close } from '@wordpress/icons';
import { UP, DOWN } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import MediaModal from './media-modal';

const EMPTY_ARRAY = [];

const matchMessage = ( message, prompt ) => {
	if ( message.type !== 'user' ) {
		return '';
	}

	if ( prompt === '' && message.content.parts[ 0 ]?.text ) {
		return message.content.parts[ 0 ].text;
	}

	for ( let j = 0; j < message.content.parts.length; j++ ) {
		if (
			message.content.parts[ j ].text &&
			message.content.parts[ j ].text.startsWith( prompt ) &&
			message.content.parts[ j ].text !== prompt
		) {
			return message.content.parts[ j ].text;
		}
	}

	return '';
};

const matchLastMessage = (
	messages,
	prompt,
	matchedIndex = -1,
	searchForwards = false
) => {
	if ( ! messages || ! messages.length ) {
		return [ -1, '' ];
	}

	if ( searchForwards ) {
		const startIndex = matchedIndex === -1 ? 0 : matchedIndex + 1;
		for ( let i = startIndex; i < messages.length; i++ ) {
			const match = matchMessage( messages[ i ], prompt );
			if ( match ) {
				return [ i, match ];
			}
		}
	} else {
		const startIndex =
			matchedIndex === -1 ? messages.length - 1 : matchedIndex - 1;
		for ( let i = startIndex; i >= 0; i-- ) {
			const match = matchMessage( messages[ i ], prompt );
			if ( match ) {
				return [ i, match ];
			}
		}
	}

	return [ -1, '' ];
};

/**
 * Renders the prompt input UI.
 *
 * @since 0.4.0
 *
 * @return {Component} The component to be rendered.
 */
export default function Input() {
	const [ prompt, setPrompt ] = useState( '' );
	const [ attachments, setAttachments ] = useState( [] );
	const [ includeHistory, setIncludeHistory ] = useState( false );
	const [ promptToMatch, setPromptToMatch ] = useState( false );
	const [ matchedIndex, setMatchedIndex ] = useState( -1 );

	const { service, model, capabilities, messages, canUploadMedia } =
		useSelect( ( select ) => {
			const { getService, getModel, getMessages } =
				select( playgroundStore );
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
						services[ currentService ].available_models[
							currentModel
						].capabilities;
				}
			}

			const { canUser } = select( coreStore );

			return {
				service: currentService,
				model: currentModel,
				capabilities: currentCapabilities,
				messages: getMessages(),
				canUploadMedia:
					canUser( 'create', {
						kind: 'root',
						name: 'media',
					} ) ?? true,
			};
		} );

	const { sendMessage } = useDispatch( playgroundStore );

	const disabled =
		! service ||
		! model ||
		( ! prompt && ( ! attachments || ! attachments.length ) );

	const sendPrompt = async () => {
		if ( disabled ) {
			return;
		}

		setPromptToMatch( false );
		setMatchedIndex( -1 );
		setPrompt( '' );
		setAttachments( [] );
		await sendMessage(
			prompt,
			capabilities.includes( enums.AiCapability.MULTIMODAL_INPUT )
				? attachments
				: undefined,
			capabilities.includes( enums.AiCapability.CHAT_HISTORY )
				? includeHistory
				: false
		);
	};

	const searchLastMessage = useCallback(
		( event ) => {
			if ( event.keyCode === UP || event.keyCode === DOWN ) {
				if ( false === promptToMatch ) {
					setPromptToMatch( prompt );
				}
				const [ foundIndex, matchedMessage ] = matchLastMessage(
					messages,
					false === promptToMatch ? prompt : promptToMatch,
					matchedIndex,
					event.keyCode === DOWN
				);
				if ( matchedMessage ) {
					setPrompt( matchedMessage );
					setMatchedIndex( foundIndex );
				}
			}
		},
		[ messages, prompt, promptToMatch, matchedIndex ]
	);

	// If the last message is a function call, allow providing JSON data as a prompt for the function response.
	const allowFunctionResponse = useMemo( () => {
		if ( ! capabilities.includes( enums.AiCapability.FUNCTION_CALLING ) ) {
			return false;
		}

		if ( ! messages || ! messages.length ) {
			return false;
		}

		const lastMessage = messages[ messages.length - 1 ];

		if ( lastMessage.type !== 'model' ) {
			return false;
		}

		return !! lastMessage.content?.parts?.some(
			( part ) => part.functionCall
		);
	}, [ capabilities, messages ] );

	const inputRef = useRef();

	useEffect( () => {
		if ( ! inputRef.current ) {
			return;
		}

		inputRef.current.focus();
	}, [ inputRef ] );

	useEffect( () => {
		if ( ! inputRef.current ) {
			return;
		}

		const inputElement = inputRef.current;
		inputElement.addEventListener( 'keydown', searchLastMessage );
		return () => {
			inputElement.removeEventListener( 'keydown', searchLastMessage );
		};
	}, [ inputRef, searchLastMessage ] );

	let inputPlaceholder = __( 'Enter AI prompt', 'ai-services' );
	if ( allowFunctionResponse && includeHistory ) {
		inputPlaceholder = __(
			'Enter AI prompt or JSON data for a function response',
			'ai-services'
		);
	} else if ( capabilities.includes( enums.AiCapability.TEXT_TO_SPEECH ) ) {
		inputPlaceholder = __(
			'Enter AI text to transform to speech',
			'ai-services'
		);
	} else if ( capabilities.includes( enums.AiCapability.IMAGE_GENERATION ) ) {
		inputPlaceholder = __(
			'Enter AI prompt to generate images',
			'ai-services'
		);
	} else if ( capabilities.includes( enums.AiCapability.TEXT_GENERATION ) ) {
		inputPlaceholder = __(
			'Enter AI prompt to generate content',
			'ai-services'
		);
	}

	const attachmentIds = useMemo(
		() => attachments.map( ( attachment ) => attachment.id ),
		[ attachments ]
	);

	const removeAttachment = ( indexToRemove ) => {
		setAttachments( ( prevAttachments ) =>
			prevAttachments.filter( ( _, index ) => index !== indexToRemove )
		);
	};

	return (
		<div className="ai-services-playground__input-backdrop">
			<div className="ai-services-playground__input-container">
				<textarea
					className="ai-services-playground__input"
					ref={ inputRef }
					placeholder={ inputPlaceholder }
					aria-label={ __( 'AI prompt', 'ai-services' ) }
					value={ prompt }
					onChange={ ( event ) => setPrompt( event.target.value ) }
					rows="2"
				/>
				<Flex direction="column" gap="2">
					{ capabilities.includes(
						enums.AiCapability.MULTIMODAL_INPUT
					) &&
						attachments.length > 0 && (
							<Flex justify="flex-start" gap="2">
								{ attachments.map( ( attachment, index ) => (
									<div
										key={ attachment.id || index }
										className="ai-services-playground__input-attachment"
									>
										<img
											className="attachment-preview"
											src={
												attachment.sizes?.thumbnail
													?.url || attachment.icon
											}
											alt={ sprintf(
												/* translators: %s: attachment filename */
												__(
													'Selected file: %s',
													'ai-services'
												),
												attachment.filename
											) }
											width="80"
											height="80"
										/>
										<button
											className="attachment-remove-button"
											aria-label={ __(
												'Remove selected media',
												'ai-services'
											) }
											onClick={ () =>
												removeAttachment( index )
											}
										>
											{ close }
										</button>
									</div>
								) ) }
							</Flex>
						) }
					{ !! ( allowFunctionResponse && ! includeHistory ) && (
						<div className="ai-services-playground__input-notices">
							<Notice status="info" isDismissible={ false }>
								{ __(
									'In order to send a function response for the received function call, you need to enable message history below.',
									'ai-services'
								) }
							</Notice>
						</div>
					) }
					<div className="ai-services-playground__input-actions">
						<div className="ai-services-playground__input-action-group">
							{ capabilities.includes(
								enums.AiCapability.MULTIMODAL_INPUT
							) &&
								canUploadMedia && (
									<MediaModal
										onSelect={ setAttachments }
										allowedTypes={ [ 'image' ] }
										multiple={ true }
										attachmentIds={ attachmentIds }
										render={ ( { open } ) => (
											<button
												className="ai-services-playground__input-action ai-services-playground__input-action--secondary"
												aria-label={ __(
													'Select media for multimodal prompt',
													'ai-services'
												) }
												onClick={ open }
											>
												{ upload }
											</button>
										) }
									/>
								) }
							{ capabilities.includes(
								enums.AiCapability.MULTIMODAL_INPUT
							) &&
								! canUploadMedia && (
									<button
										className="ai-services-playground__input-action ai-services-playground__input-action--secondary"
										aria-label={ __(
											'Missing required permissions to select media',
											'ai-services'
										) }
										disabled={ true }
										onClick={ () => {} }
									>
										{ upload }
									</button>
								) }
							{ capabilities.includes(
								enums.AiCapability.CHAT_HISTORY
							) && (
								<ToggleControl
									__nextHasNoMarginBottom
									className="ai-services-playground__input-action ai-services-playground__input-action--complex"
									label={ __(
										'Send message history with the prompt',
										'ai-services'
									) }
									checked={ includeHistory }
									onChange={ () =>
										setIncludeHistory( ! includeHistory )
									}
								/>
							) }
						</div>
						<div className="ai-services-playground__input-action-group">
							<button
								className="ai-services-playground__input-action ai-services-playground__input-action--primary"
								aria-label={ __(
									'Send AI prompt',
									'ai-services'
								) }
								disabled={ disabled }
								onClick={ sendPrompt }
							>
								{ send }
							</button>
						</div>
					</div>
				</Flex>
			</div>
		</div>
	);
}
