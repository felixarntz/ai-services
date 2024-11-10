/**
 * Internal dependencies
 */
import GenerativeAiService from './generative-ai-service';
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
	 * @param {GenerativeAiService} service             Generative AI service.
	 * @param {Object}              options             Chat options.
	 * @param {Object[]}            options.history     Chat history.
	 * @param {Object}              options.modelParams Model parameters.
	 */
	constructor( service, { history, modelParams } ) {
		this.service = service;
		this.modelParams = modelParams;

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

		const candidates = await this.service.generateText(
			contents,
			this.modelParams
		);

		// TODO: Support optional candidateFilterArgs, similar to PHP implementation.
		const responseContents = getCandidateContents( candidates );
		const responseContent = responseContents[ 0 ];

		this.history = [ ...this.history, newContent, responseContent ];

		return responseContent;
	}
}
