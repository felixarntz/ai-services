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

const RECEIVE_MESSAGE = 'RECEIVE_MESSAGE';
const RECEIVE_MESSAGES_FROM_CACHE = 'RECEIVE_MESSAGES_FROM_CACHE';
const RESET_MESSAGES = 'RESET_MESSAGES';
const SET_ACTIVE_RAW_DATA = 'SET_ACTIVE_RAW_DATA';
const LOAD_START = 'LOAD_START';
const LOAD_FINISH = 'LOAD_FINISH';

const getBase64Image = async ( url ) => {
	const data = await fetch( url );
	const blob = await data.blob();
	return new Promise( ( resolve ) => {
		const reader = new window.FileReader();
		reader.readAsDataURL( blob );
		reader.onloadend = () => {
			const base64data = reader.result;
			resolve( base64data );
		};
	} );
};

const formatNewContent = async ( prompt, attachment ) => {
	if ( ! attachment ) {
		return helpers.textToContent( prompt );
	}

	const parts = [];
	if ( prompt ) {
		parts.push( { text: prompt } );
	}
	if ( attachment ) {
		const mimeType = attachment.mime;
		const data = await getBase64Image(
			attachment.sizes?.large?.url || attachment.url
		);
		parts.push( {
			inlineData: {
				mimeType,
				data,
			},
		} );
	}

	return {
		role: enums.ContentRole.USER,
		parts,
	};
};

const initialState = {
	messages: undefined,
	loading: false,
	activeRawData: null,
};

const formatErrorContent = ( error ) => {
	return helpers.textToContent(
		sprintf( '%s', error.message || error ),
		enums.ContentRole.MODEL
	);
};

const actions = {
	/**
	 * Sends a message.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string}  prompt     Message prompt.
	 * @param {Object?} attachment Optional attachment object.
	 * @return {Function} Action creator.
	 */
	sendMessage( prompt, attachment ) {
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
			const systemInstruction = select.getSystemInstruction();
			if ( systemInstruction ) {
				modelParams.systemInstruction = systemInstruction;
			}

			const newContent = await formatNewContent( prompt, attachment );
			dispatch.receiveMessage( 'user', newContent, {
				rawData: {
					content: newContent,
					modelParams,
				},
			} );

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
				candidates = await model.generateText( newContent );

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
						name: modelSlug,
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
	 * @since n.e.x.t
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
	 * @since n.e.x.t
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
	 * @since n.e.x.t
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
	 * @since n.e.x.t
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
 * @since n.e.x.t
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
				} else if ( type === 'user' ) {
					newMessage.rawData = additionalData.rawData;
				}
			}

			const messages = [ ...state.messages, newMessage ];
			window.sessionStorage.setItem(
				'ai-services-playground-messages',
				JSON.stringify( messages )
			);
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
	 * @since n.e.x.t
	 *
	 * @return {Function} Action creator.
	 */
	getMessages() {
		return async ( { dispatch } ) => {
			const messages = window.sessionStorage.getItem(
				'ai-services-playground-messages'
			);
			dispatch.receiveMessagesFromCache(
				messages ? JSON.parse( messages ) : EMPTY_ARRAY
			);
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
