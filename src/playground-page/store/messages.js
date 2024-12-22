/**
 * External dependencies
 */
import { enums, helpers, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { resolveSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

const RECEIVE_MESSAGE = 'RECEIVE_MESSAGE';
const RESET_MESSAGES = 'RESET_MESSAGES';
const LOAD_START = 'LOAD_START';
const LOAD_FINISH = 'LOAD_FINISH';

const formatNewContent = ( content ) => {
	if ( typeof content === 'string' ) {
		return helpers.textToContent( content );
	}

	if ( Array.isArray( content ) ) {
		return {
			role: enums.ContentRole.USER,
			parts: content,
		};
	}

	return content;
};

const initialState = {
	messages: [],
	loading: false,
};

const formatErrorContent = ( error ) => {
	return helpers.textToContent(
		sprintf(
			/* translators: %s: error message */
			__( 'An error occurred: %s', 'ai-services' ),
			error.message || error
		),
		enums.ContentRole.MODEL
	);
};

const actions = {
	/**
	 * Sends a message.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string|Object|Object[]} content Chat message content.
	 * @return {Function} Action creator.
	 */
	sendMessage( content ) {
		return async ( { registry, dispatch, select } ) => {
			const serviceSlug = select.getService();
			const modelSlug = select.getModel();
			if ( ! serviceSlug || ! modelSlug ) {
				// eslint-disable-next-line no-console
				console.error( 'No AI service or model selected.' );
				return;
			}

			const newContent = formatNewContent( content );
			dispatch.receiveMessage( 'user', newContent );

			await dispatch( {
				type: LOAD_START,
			} );

			if ( registry.select( aiStore ).getServices() === undefined ) {
				await resolveSelect( aiStore ).getServices();
			}

			const modelParams = {
				feature: 'ai-playground',
				model: modelSlug,
			};
			const systemInstruction = select.getSystemInstruction();
			if ( systemInstruction ) {
				modelParams.systemInstruction = systemInstruction;
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
			if ( additionalData && type === 'model' ) {
				newMessage.service = additionalData.service;
				newMessage.model = additionalData.model;
				newMessage.rawData = additionalData.rawData;
			}
			return {
				...state,
				messages: [ ...state.messages, newMessage ],
			};
		}
		case RESET_MESSAGES: {
			return {
				...state,
				messages: [],
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

const selectors = {
	getMessages: ( state ) => {
		return state.messages;
	},

	isLoading: ( state ) => {
		return state.loading;
	},
};

const storeConfig = {
	initialState,
	actions,
	reducer,
	selectors,
};

export default storeConfig;
