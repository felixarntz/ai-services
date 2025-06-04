/**
 * WordPress dependencies
 */
import { resolveSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import ChatSession from '../classes/chat-session';
import GenerativeAiService from '../classes/generative-ai-service';
import * as enums from '../enums';
import { formatNewContent } from '../util';
import logError from '../../utils/log-error';
import type { StoreConfig, Action, ThunkArgs } from '../../utils/store-types';
import {
	Content,
	Part,
	AvailableServicesArgs,
	ChatConfigOptions,
	StartChatOptions,
} from '../types';

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	ReceiveChat = 'RECEIVE_CHAT',
	ReceiveContent = 'RECEIVE_CONTENT',
	RevertContent = 'REVERT_CONTENT',
	LoadChatStart = 'LOAD_CHAT_START',
	LoadChatFinish = 'LOAD_CHAT_FINISH',
}

type UnknownAction = Action< ActionType.Unknown >;
type ReceiveChatAction = Action<
	ActionType.ReceiveChat,
	{
		chatId: string;
		session: ChatSession;
		options: StartChatOptions;
	}
>;
type ReceiveContentAction = Action<
	ActionType.ReceiveContent,
	{
		chatId: string;
		content: Content;
	}
>;
type RevertContentAction = Action<
	ActionType.RevertContent,
	{
		chatId: string;
	}
>;
type LoadChatStartAction = Action<
	ActionType.LoadChatStart,
	{
		chatId: string;
	}
>;
type LoadChatFinishAction = Action<
	ActionType.LoadChatFinish,
	{
		chatId: string;
	}
>;

export type CombinedAction =
	| UnknownAction
	| ReceiveChatAction
	| ReceiveContentAction
	| RevertContentAction
	| LoadChatStartAction
	| LoadChatFinishAction;

export type State = {
	chatConfigs: Record< string, ChatConfigOptions >;
	chatHistories: Record< string, Content[] >;
	chatsLoading: Record< string, boolean >;
};

export type ActionCreators = typeof actions;
export type Selectors = typeof selectors;

type DispatcherArgs = ThunkArgs<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
>;

const chatSessionInstances: Record< string, ChatSession > = {};

const initialState: State = {
	chatConfigs: {},
	chatHistories: {},
	chatsLoading: {},
};

const SERVICE_ARGS: AvailableServicesArgs = {
	capabilities: [
		enums.AiCapability.TEXT_GENERATION,
		enums.AiCapability.CHAT_HISTORY,
	],
};

const EMPTY_HISTORY: Content[] = [];

/**
 * Sanitizes the chat history to remove any unsupported properties.
 *
 * @since 0.3.0
 *
 * @param history - Chat history.
 * @returns Sanitized chat history.
 */
function sanitizeHistory( history: Content[] ): Content[] {
	return history.map( ( content ) => {
		if (
			content.role &&
			content.parts &&
			Object.keys( content ).length > 2
		) {
			return {
				role: content.role,
				parts: content.parts,
			};
		}
		return content;
	} );
}

/**
 * Processes a stream of content and yields its chunks.
 *
 * @since 0.3.0
 *
 * @param responseGenerator - The generator that yields the chunks of content.
 * @param completeCallback  - Callback that is called once the generator has been processed.
 * @returns The generator that yields the chunks of content.
 */
async function* processContentStream(
	responseGenerator: AsyncGenerator< Content, void, void >,
	completeCallback: () => void
): AsyncGenerator< Content, void, void > {
	for await ( const response of responseGenerator ) {
		yield response;
	}
	completeCallback();
}

const actions = {
	/**
	 * Starts a chat session.
	 *
	 * @since 0.1.0
	 *
	 * @param chatId  - Identifier to use for the chat.
	 * @param options - Chat options.
	 * @returns Action creator.
	 */
	startChat( chatId: string, options: StartChatOptions ) {
		const { service, modelParams, history } = options;

		return async ( { dispatch, registry }: DispatcherArgs ) => {
			if ( registry.select( STORE_NAME ).getServices() === undefined ) {
				await resolveSelect( STORE_NAME ).getServices();
			}

			if (
				service &&
				! registry.select( STORE_NAME ).isServiceAvailable( service )
			) {
				logError( `The AI service ${ service } is not available.` );
				return;
			}
			if (
				! service &&
				! registry
					.select( STORE_NAME )
					.hasAvailableServices( SERVICE_ARGS )
			) {
				logError(
					'No AI service available for text generation with chat history.'
				);
				return;
			}

			dispatch( {
				type: ActionType.LoadChatStart,
				payload: { chatId },
			} );

			const aiService: GenerativeAiService = registry
				.select( STORE_NAME )
				.getAvailableService( service || SERVICE_ARGS );

			const model = aiService.getModel( modelParams );
			const session = model.startChat( sanitizeHistory( history || [] ) );

			dispatch.receiveChat( chatId, session, {
				service,
				history,
				modelParams,
			} );

			dispatch( {
				type: ActionType.LoadChatFinish,
				payload: { chatId },
			} );
		};
	},

	/**
	 * Sends a message to the chat.
	 *
	 * @since 0.1.0
	 *
	 * @param chatId  - Identifier of the chat.
	 * @param content - Chat message content.
	 * @returns Action creator.
	 */
	sendMessage( chatId: string, content: string | Part[] | Content ) {
		return async ( { dispatch }: DispatcherArgs ) => {
			const session = chatSessionInstances[ chatId ];
			if ( ! session ) {
				logError( `Chat ${ chatId } not found.` );
				return;
			}

			const newContent = formatNewContent( content );
			dispatch.receiveContent( chatId, newContent );

			dispatch( {
				type: ActionType.LoadChatStart,
				payload: { chatId },
			} );

			let response;
			try {
				response = await session.sendMessage( newContent );
			} catch ( error ) {
				dispatch.revertContent( chatId );
				dispatch( {
					type: ActionType.LoadChatFinish,
					payload: { chatId },
				} );
				throw error;
			}

			dispatch.receiveContent( chatId, response );

			dispatch( {
				type: ActionType.LoadChatFinish,
				payload: { chatId },
			} );

			return response;
		};
	},

	/**
	 * Sends a message to the chat, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param chatId  - Identifier of the chat.
	 * @param content - Chat message content.
	 * @returns Action creator.
	 */
	streamSendMessage( chatId: string, content: string | Part[] | Content ) {
		return async ( { dispatch }: DispatcherArgs ) => {
			const session = chatSessionInstances[ chatId ];
			if ( ! session ) {
				logError( `Chat ${ chatId } not found.` );
				return;
			}

			const newContent = formatNewContent( content );
			dispatch.receiveContent( chatId, newContent );

			dispatch( {
				type: ActionType.LoadChatStart,
				payload: { chatId },
			} );

			let responseGenerator: AsyncGenerator< Content, void, void >;
			try {
				responseGenerator =
					await session.streamSendMessage( newContent );
			} catch ( error ) {
				dispatch.revertContent( chatId );
				dispatch( {
					type: ActionType.LoadChatFinish,
					payload: { chatId },
				} );
				throw error;
			}

			dispatch( {
				type: ActionType.LoadChatFinish,
				payload: { chatId },
			} );

			return processContentStream( responseGenerator, () => {
				// Once the stream is complete, get the final response from the chat session and dispatch it.
				const history = session.getHistory();
				const response = { ...history[ history.length - 1 ] };
				dispatch.receiveContent( chatId, response );
			} );
		};
	},

	/**
	 * Receives a chat session.
	 *
	 * @since 0.1.0
	 *
	 * @param chatId  - Identifier to use for the chat.
	 * @param session - Chat session.
	 * @param options - Chat options.
	 * @returns Action creator.
	 */
	receiveChat(
		chatId: string,
		session: ChatSession,
		options: StartChatOptions
	) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveChat,
				payload: { chatId, session, options },
			} );
		};
	},

	/**
	 * Receives new content to append to a chat.
	 *
	 * @since 0.1.0
	 *
	 * @param chatId  - Identifier of the chat.
	 * @param content - Chat content.
	 * @returns Action creator.
	 */
	receiveContent( chatId: string, content: Content ) {
		return {
			type: ActionType.ReceiveContent,
			payload: { chatId, content },
		};
	},

	/**
	 * Reverts the last content from a chat.
	 * This is useful for undoing the last message sent.
	 *
	 * @since 0.1.0
	 *
	 * @param chatId - Identifier of the chat.
	 * @returns Action creator.
	 */
	revertContent( chatId: string ) {
		return {
			type: ActionType.RevertContent,
			payload: { chatId },
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.1.0
 *
 * @param state  - Current state.
 * @param action - Action object.
 * @returns New state.
 */
function reducer( state: State = initialState, action: CombinedAction ): State {
	switch ( action.type ) {
		case ActionType.ReceiveChat: {
			const { chatId, session, options } = action.payload;
			const { history, ...config } = options;
			chatSessionInstances[ chatId ] = session;
			return {
				...state,
				chatConfigs: {
					...state.chatConfigs,
					[ chatId ]: config,
				},
				chatHistories: {
					...state.chatHistories,
					[ chatId ]: history || [],
				},
				chatsLoading: {
					...state.chatsLoading,
					[ chatId ]: false,
				},
			};
		}
		case ActionType.ReceiveContent: {
			const { chatId, content } = action.payload;
			return {
				...state,
				chatHistories: {
					...state.chatHistories,
					[ chatId ]: [
						...( state.chatHistories[ chatId ] || [] ),
						content,
					],
				},
			};
		}
		case ActionType.RevertContent: {
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
		case ActionType.LoadChatStart: {
			const { chatId } = action.payload;
			return {
				...state,
				chatsLoading: {
					...state.chatsLoading,
					[ chatId ]: true,
				},
			};
		}
		case ActionType.LoadChatFinish: {
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

const selectors = {
	hasChat: ( state: State, chatId: string ) => {
		return !! state.chatHistories[ chatId ];
	},

	getChat: ( state: State, chatId: string ) => {
		if ( ! state.chatHistories[ chatId ] ) {
			return EMPTY_HISTORY;
		}
		return state.chatHistories[ chatId ];
	},

	getChatConfig: ( state: State, chatId: string ) => {
		if ( ! state.chatConfigs[ chatId ] ) {
			return null;
		}
		return state.chatConfigs[ chatId ];
	},

	isChatLoading: ( state: State, chatId: string ) => {
		return state.chatsLoading[ chatId ];
	},
};

const storeConfig: StoreConfig<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
> = {
	initialState,
	actions,
	reducer,
	selectors,
};

export default storeConfig;
