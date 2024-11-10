/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import { getCandidateContents } from '../helpers';
import { validateChatHistory, formatNewContent } from '../util';

/**
 * Class representing a chat session with a generative model.
 *
 * @since 0.1.0
 */
export default class ChatSession {
	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param {GenerativeAiModel} model           Generative AI model.
	 * @param {Object}            options         Chat options.
	 * @param {Object[]}          options.history Chat history.
	 */
	constructor( model, { history } ) {
		this.model = model;

		if ( history ) {
			validateChatHistory( history );
			this.history = history;
		} else {
			this.history = [];
		}
	}

	/**
	 * Gets the chat history.
	 *
	 * @since 0.1.0
	 *
	 * @return {Object[]} Chat history.
	 */
	getHistory() {
		return this.history;
	}

	/**
	 * Sends a chat message to the model.
	 *
	 * @since 0.1.0
	 *
	 * @param {string|Object|Object[]} content Chat message content.
	 * @return {Promise<Object>} The response content.
	 */
	async sendMessage( content ) {
		const newContent = formatNewContent( content );

		const contents = [ ...this.history, newContent ];

		const candidates = await this.model.generateText( contents );

		// TODO: Support optional candidateFilterArgs, similar to PHP implementation.
		const responseContents = getCandidateContents( candidates );
		const responseContent = responseContents[ 0 ];

		this.history = [ ...this.history, newContent, responseContent ];

		return responseContent;
	}
}
