/**
 * External dependencies
 */
import { enums, helpers, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { resolveSelect } from '@wordpress/data';
import { sprintf } from '@wordpress/i18n';

const EMPTY_ARRAY = [];

const SESSION_STORAGE_KEY = 'ai-services-playground-messages';

const prepareContentForCache = ( content, attachment ) => {
	return {
		...content,
		parts: content.parts.map( ( part ) => {
			/*
			 * For inline data where the attachment is known, strip the actual base64 data to save space.
			 * Otherwise, the data may be too large for session storage.
			 */
			if ( part.inlineData && part.inlineData.data && attachment ) {
				const { data, ...otherInlineData } = part.inlineData;
				return {
					...part,
					inlineData: {
						...otherInlineData,
					},
				};
			}
			return part;
		} ),
	};
};

const parseContentFromCache = async ( content, attachment ) => {
	return {
		...content,
		parts: await Promise.all(
			content.parts.map( async ( part ) => {
				// For inline data where the attachment is known but base64 data was stripped before cache, restore it.
				if ( part.inlineData && ! part.inlineData.data && attachment ) {
					return {
						...part,
						inlineData: {
							...part.inlineData,
							data: await helpers.base64EncodeFile(
								attachment.sizes?.large?.url || attachment.url
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
	// We can only optimize messages with inline media if they have the attachment specified.
	if ( ! message.attachment ) {
		return message;
	}

	const prepared = {
		...message,
		content: prepareContentForCache( message.content, message.attachment ),
	};
	if ( prepared.rawData && prepared.rawData.content ) {
		prepared.rawData = {
			...prepared.rawData,
			content: prepared.content,
		};
	}
	return prepared;
};

const parseMessageFromCache = async ( message ) => {
	// We can only parse messages with inline media if they have the attachment specified.
	if ( ! message.attachment ) {
		return message;
	}

	const parsed = {
		...message,
		content: await parseContentFromCache(
			message.content,
			message.attachment
		),
	};
	if ( parsed.rawData && parsed.rawData.content ) {
		parsed.rawData = {
			...parsed.rawData,
			content: parsed.content,
		};
	}
	return parsed;
};

const retrieveMessages = async () => {
	const messagesJson = window.sessionStorage.getItem( SESSION_STORAGE_KEY );
	if ( messagesJson ) {
		const messages = JSON.parse( messagesJson );
		return await Promise.all( messages.map( parseMessageFromCache ) );
	}
	return EMPTY_ARRAY;
};

const storeMessages = ( messages ) => {
	window.sessionStorage.setItem(
		SESSION_STORAGE_KEY,
		JSON.stringify( messages.map( prepareMessageForCache ) )
	);
};

const clearMessages = () => {
	window.sessionStorage.removeItem( SESSION_STORAGE_KEY );
};

const formatNewContent = async (
	prompt,
	attachment,
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

	if ( attachment ) {
		return helpers.textAndAttachmentToContent( prompt, attachment );
	}
	return helpers.textToContent( prompt );
};

const formatErrorContent = ( error ) => {
	return helpers.textToContent(
		sprintf( '%s', error.message || error ),
		enums.ContentRole.MODEL
	);
};

const getTools = ( functionDeclarations, selectedFunctionDeclarationNames ) => {
	const selectedFunctionDeclarations = functionDeclarations?.filter(
		( declaration ) =>
			selectedFunctionDeclarationNames &&
			selectedFunctionDeclarationNames.includes( declaration.name )
	);

	if ( selectedFunctionDeclarations && selectedFunctionDeclarations.length ) {
		return [ { functionDeclarations: selectedFunctionDeclarations } ];
	}

	return null;
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

const RECEIVE_MESSAGE = 'RECEIVE_MESSAGE';
const RECEIVE_MESSAGES_FROM_CACHE = 'RECEIVE_MESSAGES_FROM_CACHE';
const RESET_MESSAGES = 'RESET_MESSAGES';
const SET_ACTIVE_RAW_DATA = 'SET_ACTIVE_RAW_DATA';
const LOAD_START = 'LOAD_START';
const LOAD_FINISH = 'LOAD_FINISH';

const initialState = {
	messages: undefined,
	loading: false,
	activeRawData: null,
};

const actions = {
	/**
	 * Sends a message.
	 *
	 * @since 0.4.0
	 *
	 * @param {string}  prompt         Message prompt.
	 * @param {Object?} attachment     Optional attachment object.
	 * @param {boolean} includeHistory Whether to include the message history before the prompt. Default false.
	 * @return {Function} Action creator.
	 */
	sendMessage( prompt, attachment, includeHistory ) {
		return async ( { registry, dispatch, select } ) => {
			const serviceSlug = select.getService();
			const modelSlug = select.getModel();
			if ( ! serviceSlug || ! modelSlug ) {
				// eslint-disable-next-line no-console
				console.error( 'No AI service or model selected.' );
				return;
			}

			const modelParams = {
				feature: 'ai-playground',
				model: modelSlug,
			};

			const generationConfig = {};
			const paramKeys = [ 'maxOutputTokens', 'temperature', 'topP' ];
			paramKeys.forEach( ( key ) => {
				const value = select.getModelParam( key );
				if ( value ) {
					generationConfig[ key ] = Number( value );
				}
			} );
			if ( Object.keys( generationConfig ).length ) {
				modelParams.generationConfig = generationConfig;
			}

			const systemInstruction = select.getSystemInstruction();
			if ( systemInstruction ) {
				modelParams.systemInstruction = systemInstruction;
			}

			const tools = getTools(
				select.getFunctionDeclarations(),
				select.getSelectedFunctionDeclarations()
			);
			if ( tools ) {
				modelParams.tools = tools;
			}

			const originalMessages = select.getMessages();

			const newContent = await formatNewContent(
				prompt,
				attachment,
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

			const additionalPromptData = {
				rawData: {
					content: contentToSend,
					modelParams,
				},
			};
			if ( attachment ) {
				additionalPromptData.attachment = attachment;
			}

			dispatch.receiveMessage( 'user', newContent, additionalPromptData );

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

			let candidates;
			try {
				candidates = await model.generateText( contentToSend );

				const responseContent =
					helpers.getCandidateContents( candidates )[ 0 ];
				dispatch.receiveMessage( 'model', responseContent, {
					service: {
						slug: serviceSlug,
						name: registry
							.select( aiStore )
							.getServiceName( serviceSlug ),
					},
					model: {
						slug: modelSlug,
						name: model.name || modelSlug,
					},
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
	 * Sets the active raw data (to display in a modal).
	 *
	 * @since 0.4.0
	 *
	 * @param {Object} rawData Raw data to display.
	 * @return {Object} Action creator.
	 */
	setActiveRawData( rawData ) {
		return {
			type: SET_ACTIVE_RAW_DATA,
			payload: { rawData },
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
				if ( type === 'model' ) {
					newMessage.service = additionalData.service;
					newMessage.model = additionalData.model;
					newMessage.rawData = additionalData.rawData;
					if ( additionalData.attachment ) {
						newMessage.attachment = additionalData.attachment;
					}
				} else if ( type === 'user' ) {
					newMessage.rawData = additionalData.rawData;
					if ( additionalData.attachment ) {
						newMessage.attachment = additionalData.attachment;
					}
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
		case SET_ACTIVE_RAW_DATA: {
			const { rawData } = action.payload;
			return {
				...state,
				activeRawData: rawData,
			};
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

	getActiveRawData: ( state ) => {
		return state.activeRawData;
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
