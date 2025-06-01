/**
 * External dependencies
 */
import { enums, helpers, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { resolveSelect } from '@wordpress/data';
import { __, _x, sprintf } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';
import { uploadMedia } from '@wordpress/media-utils';

const EMPTY_ARRAY = [];

const FEATURE_SLUG = 'ai-playground';
const HISTORY_SLUG = 'default';

const prepareContentForCache = ( content, attachments ) => {
	return {
		...content,
		parts: content.parts.map( ( part, partIndex ) => {
			/*
			 * For inline data where the attachment is known, strip the actual base64 data to save space.
			 * Otherwise, the data may be too large for session storage.
			 */
			if (
				part.inlineData &&
				part.inlineData.data &&
				attachments[ partIndex ]
			) {
				const { data, ...otherInlineData } = part.inlineData;
				return {
					...part,
					inlineData: {
						...otherInlineData,
						data: '',
					},
				};
			}
			return part;
		} ),
	};
};

const parseContentFromCache = async ( content, attachments ) => {
	return {
		...content,
		parts: await Promise.all(
			content.parts.map( async ( part, partIndex ) => {
				// For inline data where the attachment is known but base64 data was stripped before cache, restore it.
				if (
					part.inlineData &&
					! part.inlineData.data &&
					attachments[ partIndex ]
				) {
					return {
						...part,
						inlineData: {
							...part.inlineData,
							data: await helpers.fileToBase64DataUrl(
								attachments[ partIndex ].sizes?.large?.url ||
									attachments[ partIndex ].url
							),
						},
					};
				}
				return part;
			} )
		),
	};
};

const prepareMessageForCache = ( message ) => {
	// Migrate old "attachment" property to new "attachments" array on-the-fly.
	if ( ! message.attachments && message.attachment ) {
		let partIndex = message.content.parts.findIndex(
			( part ) => part.inlineData
		);
		if ( partIndex === -1 ) {
			partIndex = 0;
		}
		message = {
			...message,
			attachments: getFreshPartsAttachments(
				message,
				partIndex,
				message.attachment
			),
		};
		delete message.attachment;
	}

	// We can only optimize messages with inline media if they have the attachments specified.
	if ( ! message.attachments ) {
		return message;
	}

	const prepared = {
		...message,
		content: prepareContentForCache( message.content, message.attachments ),
	};
	if ( prepared.rawData ) {
		/*
		 * For a user message, the content is directly in rawData, which is the request parameters object.
		 * For a model message, the content is within the first item of rawData, which is the candidates array.
		 */
		if ( prepared.rawData.content ) {
			prepared.rawData = {
				...prepared.rawData,
				content: prepared.content,
			};
		} else if ( prepared.rawData[ 0 ]?.content ) {
			prepared.rawData = [ ...prepared.rawData ];
			prepared.rawData[ 0 ] = {
				...prepared.rawData[ 0 ],
				content: prepared.content,
			};
		}
	}
	return prepared;
};

const parseMessageFromCache = async ( message ) => {
	// Migrate old "attachment" property to new "attachments" array on-the-fly.
	if ( ! message.attachments && message.attachment ) {
		let partIndex = message.content.parts.findIndex(
			( part ) => part.inlineData
		);
		if ( partIndex === -1 ) {
			partIndex = 0;
		}
		message = {
			...message,
			attachments: getFreshPartsAttachments(
				message,
				partIndex,
				message.attachment
			),
		};
		delete message.attachment;
	}

	// We can only parse messages with inline media if they have the attachments specified.
	if ( ! message.attachments ) {
		return message;
	}

	const parsed = {
		...message,
		content: await parseContentFromCache(
			message.content,
			message.attachments
		),
	};
	if ( parsed.rawData ) {
		/*
		 * For a user message, the content is directly in rawData, which is the request parameters object.
		 * For a model message, the content is within the first item of rawData, which is the candidates array.
		 */
		if ( parsed.rawData.content ) {
			parsed.rawData = {
				...parsed.rawData,
				content: parsed.content,
			};
		} else if ( parsed.rawData[ 0 ]?.content ) {
			parsed.rawData = [ ...parsed.rawData ];
			parsed.rawData[ 0 ] = {
				...parsed.rawData[ 0 ],
				content: parsed.content,
			};
		}
	}
	return parsed;
};

const retrieveMessages = async () => {
	const history = await helpers
		.historyPersistence()
		.loadHistory( FEATURE_SLUG, HISTORY_SLUG );
	if ( history && history.entries ) {
		const entries = await Promise.all(
			history.entries.map( parseMessageFromCache )
		);

		/*
		 * For backward compatibility, populate the additional data for
		 * `foundationalCapability`, `service`, and `model` for each user message that are missing them,
		 * using a best guess based on the subsequent model response.
		 */
		for ( let index = 0; index < entries.length; index++ ) {
			const entry = entries[ index ];
			if (
				entry.type === 'user' &&
				( ! entry.foundationalCapability ||
					! entry.service ||
					! entry.model )
			) {
				const nextEntry = entries[ index + 1 ];
				if ( nextEntry && nextEntry.type === 'model' ) {
					if ( nextEntry.content.parts?.[ 0 ]?.inlineData ) {
						entry.foundationalCapability =
							enums.AiCapability.IMAGE_GENERATION;
					} else {
						entry.foundationalCapability =
							enums.AiCapability.TEXT_GENERATION;
					}
					entry.service = nextEntry.service;
					entry.model = nextEntry.model;
				} else {
					entry.foundationalCapability =
						enums.AiCapability.TEXT_GENERATION;
				}
				entries[ index ] = entry;
			} else if (
				entry.type === 'model' &&
				! entry.foundationalCapability
			) {
				if ( entry.content.parts?.[ 0 ]?.inlineData ) {
					entry.foundationalCapability =
						enums.AiCapability.IMAGE_GENERATION;
				} else {
					entry.foundationalCapability =
						enums.AiCapability.TEXT_GENERATION;
				}
				entries[ index ] = entry;
			}
		}

		return entries;
	}
	return EMPTY_ARRAY;
};

const storeMessages = async ( messages ) => {
	const history = {
		feature: FEATURE_SLUG,
		slug: HISTORY_SLUG,
		lastUpdated: '',
		entries: messages.map( prepareMessageForCache ),
	};
	await helpers.historyPersistence().saveHistory( history );
};

const clearMessages = async () => {
	await helpers
		.historyPersistence()
		.clearHistory( FEATURE_SLUG, HISTORY_SLUG );
};

const formatNewContent = async (
	prompt,
	attachments,
	includeHistory,
	messages
) => {
	if ( includeHistory ) {
		// See if the prompt is JSON in response to a function call in the last message.
		const lastMessageFunctionCall = getLastMessageFunctionCall( messages );
		if ( lastMessageFunctionCall ) {
			let responseData;
			try {
				responseData = JSON.parse( prompt.trim() );
			} catch ( err ) {
				// Ignore errors.
			}
			if ( responseData ) {
				const functionResponse = {};
				if ( lastMessageFunctionCall.functionCall.id ) {
					functionResponse.id =
						lastMessageFunctionCall.functionCall.id;
				}
				if ( lastMessageFunctionCall.functionCall.name ) {
					functionResponse.name =
						lastMessageFunctionCall.functionCall.name;
				}
				functionResponse.response = responseData;
				return {
					role: enums.ContentRole.USER,
					parts: [
						{
							functionResponse,
						},
					],
				};
			}
		}
	}

	if ( attachments && attachments.length ) {
		return helpers.textAndAttachmentsToContent( prompt, attachments );
	}
	return helpers.textToContent( prompt );
};

const formatErrorContent = ( error ) => {
	return helpers.textToContent(
		sprintf( '%s', error.message || error ),
		enums.ContentRole.MODEL
	);
};

const getTools = (
	functionDeclarations,
	selectedFunctionDeclarationNames,
	additionalCapabilities
) => {
	const selectedFunctionDeclarations = functionDeclarations?.filter(
		( declaration ) =>
			selectedFunctionDeclarationNames &&
			selectedFunctionDeclarationNames.includes( declaration.name )
	);

	const tools = [];
	if ( selectedFunctionDeclarations && selectedFunctionDeclarations.length ) {
		tools.push( { functionDeclarations: selectedFunctionDeclarations } );
	}
	if (
		additionalCapabilities &&
		additionalCapabilities.includes( enums.AiCapability.WEB_SEARCH )
	) {
		tools.push( { webSearch: {} } );
	}

	return tools.length > 0 ? tools : null;
};

const getLastMessageFunctionCall = ( messages ) => {
	if ( ! messages || ! messages.length ) {
		return null;
	}

	const lastMessage = messages[ messages.length - 1 ];
	if ( lastMessage.type !== 'model' ) {
		return null;
	}

	return lastMessage.content?.parts?.find( ( part ) => part.functionCall );
};

const generateFilename = ( partIndex, mimeType, serviceSlug, modelSlug ) => {
	let extension = mimeType.split( '/' )[ 1 ];
	if ( extension === 'jpeg' ) {
		extension = 'jpg';
	}

	let source = '';
	if ( serviceSlug ) {
		source = `${ serviceSlug }-`;
		if ( modelSlug ) {
			source += `${ modelSlug }-`;
		}
	}

	const now = new Date();
	const dateSuffix = now
		.toISOString()
		.substring( 0, 19 )
		.replace( 'T', '-' )
		.replace( /:/g, '' );

	return `ai-generated-${ partIndex }-${ source }${ dateSuffix }.${ extension }`;
};

const getFreshPartsAttachments = ( message, partIndex, attachment ) => {
	const attachments = [ ...( message.attachments || [] ) ];
	if ( attachments.length < message.content.parts.length ) {
		const missingIndexes =
			message.content.parts.length - attachments.length;
		for ( let i = 0; i < missingIndexes; i++ ) {
			attachments.push( null );
		}
	}
	attachments[ partIndex ] = attachment;
	return attachments;
};

const RECEIVE_MESSAGE = 'RECEIVE_MESSAGE';
const RECEIVE_MESSAGES_FROM_CACHE = 'RECEIVE_MESSAGES_FROM_CACHE';
const RESET_MESSAGES = 'RESET_MESSAGES';
const SET_ACTIVE_MESSAGE = 'SET_ACTIVE_MESSAGE';
const SET_MESSAGE_ATTACHMENT = 'SET_MESSAGE_ATTACHMENT';
const LOAD_START = 'LOAD_START';
const LOAD_FINISH = 'LOAD_FINISH';

const UPLOAD_ATTACHMENT_NOTICE_ID = 'UPLOAD_ATTACHMENT_NOTICE_ID';

const initialState = {
	messages: undefined,
	loading: false,
	activeMessage: null,
};

const actions = {
	/**
	 * Sends a message.
	 *
	 * @since 0.4.0
	 * @since 0.6.0 Now expects an array of attachments instead of a single attachment.
	 *
	 * @param {string}   prompt         Message prompt.
	 * @param {Object[]} attachments    Optional array of attachment objects.
	 * @param {boolean}  includeHistory Whether to include the message history before the prompt. Default false.
	 * @return {Function} Action creator.
	 */
	sendMessage( prompt, attachments, includeHistory ) {
		return async ( { registry, dispatch, select } ) => {
			const serviceSlug = select.getService();
			const modelSlug = select.getModel();
			if ( ! serviceSlug || ! modelSlug ) {
				// eslint-disable-next-line no-console
				console.error( 'No AI service or model selected.' );
				return;
			}

			const modelParams = {
				feature: FEATURE_SLUG,
				model: modelSlug,
			};

			const foundationalCapability = select.getFoundationalCapability();
			const additionalCapabilities = select.getAdditionalCapabilities();

			const generationConfig = {};
			if (
				foundationalCapability === enums.AiCapability.IMAGE_GENERATION
			) {
				const aspectRatio = select.getModelParam( 'aspectRatio' );
				if ( aspectRatio ) {
					generationConfig.aspectRatio = aspectRatio;
				}
			} else if (
				foundationalCapability === enums.AiCapability.TEXT_GENERATION
			) {
				const paramKeys = [ 'maxOutputTokens', 'temperature', 'topP' ];
				paramKeys.forEach( ( key ) => {
					const value = select.getModelParam( key );
					if ( value ) {
						generationConfig[ key ] = Number( value );
					}
				} );

				if (
					additionalCapabilities &&
					additionalCapabilities.includes(
						enums.AiCapability.MULTIMODAL_OUTPUT
					)
				) {
					const outputModalities =
						select.getModelParam( 'outputModalities' );
					if (
						Array.isArray( outputModalities ) &&
						outputModalities.length
					) {
						generationConfig.outputModalities = outputModalities;
					}
				}
			}
			if ( Object.keys( generationConfig ).length ) {
				modelParams.generationConfig = generationConfig;
			}

			const systemInstruction = select.getSystemInstruction();
			if ( systemInstruction ) {
				modelParams.systemInstruction = systemInstruction;
			}

			if (
				foundationalCapability === enums.AiCapability.TEXT_GENERATION
			) {
				const tools = getTools(
					select.getFunctionDeclarations(),
					select.getSelectedFunctionDeclarations(),
					additionalCapabilities
				);
				if ( tools ) {
					modelParams.tools = tools;
				}
			}

			const originalMessages = select.getMessages();

			const newContent = await formatNewContent(
				prompt,
				attachments,
				includeHistory,
				originalMessages
			);

			let contentToSend = newContent;
			if ( includeHistory ) {
				if ( originalMessages && originalMessages.length ) {
					contentToSend = [
						...originalMessages.map(
							( message ) => message.content
						),
						newContent,
					];
				}
			}

			await dispatch( {
				type: LOAD_START,
			} );

			if ( registry.select( aiStore ).getServices() === undefined ) {
				await resolveSelect( aiStore ).getServices();
			}

			const service = registry
				.select( aiStore )
				.getAvailableService( serviceSlug );
			const model = service.getModel( modelParams );

			const additionalData = {
				foundationalCapability,
				service: {
					slug: serviceSlug,
					name: service.metadata?.name || serviceSlug,
				},
				model: {
					slug: modelSlug,
					name: model.metadata?.name || modelSlug,
				},
			};

			const additionalPromptData = {
				...additionalData,
				rawData: {
					content: contentToSend,
					modelParams,
				},
			};
			if ( attachments && attachments.length ) {
				additionalPromptData.attachments = [
					null, // Based on `formatNewContent()`, the first part is always text, i.e. no related attachment.
					...attachments,
				];
			}

			dispatch.receiveMessage( 'user', newContent, additionalPromptData );

			let candidates;
			try {
				if (
					foundationalCapability ===
					enums.AiCapability.IMAGE_GENERATION
				) {
					candidates = await model.generateImage( contentToSend );
				} else {
					candidates = await model.generateText( contentToSend );
				}

				const responseContent =
					helpers.getCandidateContents( candidates )[ 0 ];
				dispatch.receiveMessage( 'model', responseContent, {
					...additionalData,
					rawData: candidates,
				} );
			} catch ( error ) {
				dispatch.receiveMessage( 'error', formatErrorContent( error ) );
			}

			await dispatch( {
				type: LOAD_FINISH,
			} );

			return candidates;
		};
	},

	/**
	 * Uploads inline data of a specific message to the media library.
	 *
	 * @since 0.5.0
	 *
	 * @param {number} index      The index of the message.
	 * @param {number} partIndex  The index of the part within the message.
	 * @param {Object} inlineData The inline data object.
	 * @return {Function} Action creator.
	 */
	uploadAttachment( index, partIndex, inlineData ) {
		return async ( { dispatch, registry, select } ) => {
			const messages = select.getMessages();
			const message = messages?.[ index ];
			if ( ! message ) {
				return;
			}

			// Sanity check that it's the correct message.
			const inlineDataPart = message.content.parts?.[ partIndex ];
			if ( inlineDataPart?.inlineData?.data !== inlineData.data ) {
				return;
			}

			const fileBlob = await helpers.base64DataUrlToBlob(
				helpers.base64DataToBase64DataUrl(
					inlineData.data,
					inlineData.mimeType
				)
			);
			const file = new File(
				[ fileBlob ],
				generateFilename(
					partIndex,
					fileBlob.type,
					message.service?.slug,
					message.model?.slug
				),
				{
					type: fileBlob.type,
					lastModified: new Date().getTime(),
				}
			);

			const attachmentData = {};
			if ( message.type === 'model' ) {
				const previousMessage = messages?.[ index - 1 ];
				if ( previousMessage && previousMessage.type === 'user' ) {
					const prompt = helpers.contentToText(
						previousMessage.content
					);
					if ( prompt ) {
						attachmentData.caption = sprintf(
							/* translators: %s: prompt text */
							_x(
								'Generated for prompt: %s',
								'attachment caption',
								'ai-services'
							),
							prompt
						);
					}
				}
			}

			return new Promise( ( resolve ) => {
				uploadMedia( {
					filesList: [ file ],
					additionalData: attachmentData,
					onFileChange: ( [ attachment ] ) => {
						if ( ! attachment ) {
							registry
								.dispatch( noticesStore )
								.createErrorNotice(
									__( 'Saving file failed.', 'ai-services' ),
									{
										id: UPLOAD_ATTACHMENT_NOTICE_ID,
										type: 'snackbar',
										speak: true,
									}
								);
							resolve( null );
							return;
						}
						if ( attachment.id ) {
							dispatch.setMessageAttachment(
								index,
								partIndex,
								attachment
							);
							registry
								.dispatch( noticesStore )
								.createSuccessNotice(
									__(
										'File saved to media library.',
										'ai-services'
									),
									{
										id: UPLOAD_ATTACHMENT_NOTICE_ID,
										type: 'snackbar',
										speak: true,
									}
								);
							resolve( attachment );
						}
					},
					onError: ( err ) => {
						registry.dispatch( noticesStore ).createErrorNotice(
							sprintf(
								/* translators: %s: error message */
								__(
									'Saving file failed with error: %s',
									'ai-services'
								),
								err.message || err
							),
							{
								id: UPLOAD_ATTACHMENT_NOTICE_ID,
								type: 'snackbar',
								speak: true,
							}
						);
						resolve( null );
					},
				} );
			} );
		};
	},

	/**
	 * Receives new content to append to the list of messages.
	 *
	 * @since 0.4.0
	 *
	 * @param {string} type           Message type. Either 'user', 'model', or 'error'.
	 * @param {Object} content        Message content.
	 * @param {Object} additionalData Additional data to include with the message.
	 * @return {Object} Action creator.
	 */
	receiveMessage( type, content, additionalData = {} ) {
		return {
			type: RECEIVE_MESSAGE,
			payload: { type, content, additionalData },
		};
	},

	/**
	 * Receives messages from cache to restore the session.
	 *
	 * @since 0.4.0
	 *
	 * @param {Object[]} messages Messages to restore.
	 * @return {Object} Action creator.
	 */
	receiveMessagesFromCache( messages ) {
		return {
			type: RECEIVE_MESSAGES_FROM_CACHE,
			payload: { messages },
		};
	},

	/**
	 * Resets all messages, effectively deleting them to start a new session.
	 *
	 * @since 0.4.0
	 *
	 * @return {Object} Action creator.
	 */
	resetMessages() {
		return {
			type: RESET_MESSAGES,
			payload: {},
		};
	},

	/**
	 * Sets the active message (to display a modal for it).
	 *
	 * @since 0.6.0
	 *
	 * @param {Object} message Message to display.
	 * @return {Object} Action creator.
	 */
	setActiveMessage( message ) {
		return {
			type: SET_ACTIVE_MESSAGE,
			payload: { message },
		};
	},

	/**
	 * Sets the attachment for a message.
	 *
	 * @since 0.5.0
	 *
	 * @param {number} index      The index of the message.
	 * @param {number} partIndex  The index of the part within the message.
	 * @param {Object} attachment The attachment object.
	 * @return {Object} Action creator.
	 */
	setMessageAttachment( index, partIndex, attachment ) {
		return {
			type: SET_MESSAGE_ATTACHMENT,
			payload: { index, partIndex, attachment },
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.4.0
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action object.
 * @return {Object} New state.
 */
function reducer( state = initialState, action ) {
	switch ( action.type ) {
		case RECEIVE_MESSAGE: {
			const { type, content, additionalData } = action.payload;
			const newMessage = { type, content };
			if ( additionalData ) {
				newMessage.foundationalCapability =
					additionalData.foundationalCapability;
				newMessage.service = additionalData.service;
				newMessage.model = additionalData.model;
				newMessage.rawData = additionalData.rawData;
				if ( additionalData.attachments ) {
					newMessage.attachments = additionalData.attachments;
				}
			}

			const messages = [ ...state.messages, newMessage ];
			storeMessages( messages );
			return {
				...state,
				messages,
			};
		}
		case RECEIVE_MESSAGES_FROM_CACHE: {
			const { messages } = action.payload;
			return {
				...state,
				messages,
			};
		}
		case RESET_MESSAGES: {
			clearMessages();
			return {
				...state,
				messages: [],
			};
		}
		case SET_ACTIVE_MESSAGE: {
			const { message } = action.payload;
			return {
				...state,
				activeMessage: message,
			};
		}
		case SET_MESSAGE_ATTACHMENT: {
			const { index, partIndex, attachment } = action.payload;
			if ( state.messages?.[ index ] ) {
				const messages = [ ...state.messages ];
				messages[ index ] = {
					...messages[ index ],
					attachments: getFreshPartsAttachments(
						messages[ index ],
						partIndex,
						attachment
					),
				};
				storeMessages( messages );
				return {
					...state,
					messages,
				};
			}
			return state;
		}
		case LOAD_START: {
			return {
				...state,
				loading: true,
			};
		}
		case LOAD_FINISH: {
			return {
				...state,
				loading: false,
			};
		}
	}

	return state;
}

const resolvers = {
	/**
	 * Retrieves messages from session storage.
	 *
	 * @since 0.4.0
	 *
	 * @return {Function} Action creator.
	 */
	getMessages() {
		return async ( { dispatch } ) => {
			const messages = await retrieveMessages();
			dispatch.receiveMessagesFromCache( messages );
		};
	},
};

const selectors = {
	getMessages: ( state ) => {
		return state.messages || EMPTY_ARRAY;
	},

	isLoading: ( state ) => {
		return state.loading || state.messages === undefined;
	},

	getActiveMessage: ( state ) => {
		return state.activeMessage;
	},
};

const storeConfig = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
