/**
 * WordPress dependencies
 */
import { resolveSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import { ChatSession } from './generative-ai-service';
import * as enums from './enums';
import { formatNewContent } from './util';

const RECEIVE_CHAT = 'RECEIVE_CHAT';
const RECEIVE_CONTENT = 'RECEIVE_CONTENT';
const REVERT_CONTENT = 'REVERT_CONTENT';
const LOAD_CHAT_START = 'LOAD_CHAT_START';
const LOAD_CHAT_FINISH = 'LOAD_CHAT_FINISH';

const SERVICE_ARGS = {
	capabilities: [ enums.AiCapability.TEXT_GENERATION ],
};

const chatSessionInstances = {};

const initialState = {
	chatConfigs: {},
	chatHistories: {},
	chatsLoading: {},
};

const actions = {
	/**
	 * Starts a chat session.
	 *
	 * @since 0.1.0
	 *
	 * @param {string} chatId              Identifier to use for the chat.
	 * @param {Object} options             Chat options.
	 * @param {string} options.service     AI service to use.
	 * @param {Object} options.modelParams Model parameters (including optional model slug).
	 * @return {Function} Action creator.
	 */
	startChat( chatId, { service, modelParams } ) {
		return async ( { dispatch, select } ) => {
			if ( select.getServices() === undefined ) {
				await resolveSelect( STORE_NAME ).getServices();
			}

			if ( service && ! select.isServiceAvailable( service ) ) {
				// eslint-disable-next-line no-console
				console.error(
					`The AI service ${ service } is not available.`
				);
				return;
			}
			if ( ! service && ! select.hasAvailableServices( SERVICE_ARGS ) ) {
				// eslint-disable-next-line no-console
				console.error( 'No AI service available for text generation.' );
				return;
			}

			await dispatch( {
				type: LOAD_CHAT_START,
				payload: { chatId },
			} );

			const aiService = select.getAvailableService(
				service || SERVICE_ARGS
			);

			// TODO: Support history persistence.
			const history = [];

			const session = await aiService.startChat( history, modelParams );

			dispatch.receiveChat( chatId, {
				session,
				service,
				history,
				modelParams,
			} );

			await dispatch( {
				type: LOAD_CHAT_FINISH,
				payload: { chatId },
			} );
		};
	},

	/**
	 * Sends a message to the chat.
	 *
	 * @since 0.1.0
	 *
	 * @param {string}                 chatId  Identifier of the chat.
	 * @param {string|Object|Object[]} content Chat message content.
	 * @return {Function} Action creator.
	 */
	sendMessage( chatId, content ) {
		return async ( { dispatch } ) => {
			const session = chatSessionInstances[ chatId ];
			if ( ! session ) {
				// eslint-disable-next-line no-console
				console.error( `Chat ${ chatId } not found.` );
				return;
			}

			const newContent = formatNewContent( content );
			dispatch.receiveContent( chatId, newContent );

			await dispatch( {
				type: LOAD_CHAT_START,
				payload: { chatId },
			} );

			let response;
			try {
				response = await session.sendMessage( newContent );
			} catch ( error ) {
				console.error( error?.message || error ); // eslint-disable-line no-console
			}

			if ( response ) {
				dispatch.receiveContent( chatId, response );
			} else {
				dispatch.revertContent( chatId );
			}

			await dispatch( {
				type: LOAD_CHAT_FINISH,
				payload: { chatId },
			} );

			return response;
		};
	},

	/**
	 * Receives a chat session.
	 *
	 * @since 0.1.0
	 *
	 * @param {string}      chatId              Identifier to use for the chat.
	 * @param {Object}      options             Chat options.
	 * @param {ChatSession} options.session     Chat session.
	 * @param {string}      options.service     AI service to use.
	 * @param {Object}      options.history     Chat history.
	 * @param {Object}      options.modelParams Model parameters.
	 * @return {Object} Action creator.
	 */
	receiveChat( chatId, { session, service, history, modelParams } ) {
		return {
			type: RECEIVE_CHAT,
			payload: { chatId, session, service, history, modelParams },
		};
	},

	/**
	 * Receives new content to append to a chat.
	 *
	 * @since 0.1.0
	 *
	 * @param {string} chatId  Identifier of the chat.
	 * @param {Object} content Chat content.
	 * @return {Object} Action creator.
	 */
	receiveContent( chatId, content ) {
		return {
			type: RECEIVE_CONTENT,
			payload: { chatId, content },
		};
	},

	/**
	 * Reverts the last content from a chat.
	 * This is useful for undoing the last message sent.
	 *
	 * @since 0.1.0
	 *
	 * @param {string} chatId Identifier of the chat.
	 * @return {Object} Action creator.
	 */
	revertContent( chatId ) {
		return {
			type: REVERT_CONTENT,
			payload: { chatId },
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.1.0
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action object.
 * @return {Object} New state.
 */
function reducer( state = initialState, action ) {
	switch ( action.type ) {
		case RECEIVE_CHAT: {
			const { chatId, session, service, history, modelParams } =
				action.payload;
			chatSessionInstances[ chatId ] = session;
			return {
				...state,
				chatConfigs: {
					...state.chatConfigs,
					[ chatId ]: {
						service,
						modelParams,
					},
				},
				chatHistories: {
					...state.chatHistories,
					[ chatId ]: history,
				},
				chatsLoading: {
					...state.chatsLoading,
					[ chatId ]: false,
				},
			};
		}
		case RECEIVE_CONTENT: {
			const { chatId, content } = action.payload;
			return {
				...state,
				chatHistories: {
					...state.chatHistories,
					[ chatId ]: [ ...state.chatHistories[ chatId ], content ],
				},
			};
		}
		case REVERT_CONTENT: {
			const { chatId } = action.payload;
			const history = state.chatHistories[ chatId ];
			if ( ! history || history.length < 1 ) {
				return state;
			}
			return {
				...state,
				chatHistories: {
					...state.chatHistories,
					[ chatId ]:
						history.length === 1 ? [] : history.slice( 0, -1 ),
				},
			};
		}
		case LOAD_CHAT_START: {
			const { chatId } = action.payload;
			return {
				...state,
				chatsLoading: {
					...state.chatsLoading,
					[ chatId ]: true,
				},
			};
		}
		case LOAD_CHAT_FINISH: {
			const { chatId } = action.payload;
			return {
				...state,
				chatsLoading: {
					...state.chatsLoading,
					[ chatId ]: false,
				},
			};
		}
	}

	return state;
}

const resolvers = {};

const selectors = {
	getChat: ( state, chatId ) => {
		if ( ! state.chatHistories[ chatId ] ) {
			return null;
		}
		return state.chatHistories[ chatId ];
	},

	getChatConfig: ( state, chatId ) => {
		if ( ! state.chatConfigs[ chatId ] ) {
			return null;
		}
		return state.chatConfigs[ chatId ];
	},

	isChatLoading: ( state, chatId ) => {
		return state.chatsLoading[ chatId ];
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
