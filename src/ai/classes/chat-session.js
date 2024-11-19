/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import { getCandidateContents, processCandidatesStream } from '../helpers';
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

	/**
	 * Sends a chat message to the model, streaming the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string|Object|Object[]} content Chat message content.
	 * @return {Promise<Object>} The generator that yields chunks of the response content.
	 */
	async streamSendMessage( content ) {
		const newContent = formatNewContent( content );

		const contents = [ ...this.history, newContent ];

		const candidatesGenerator =
			await this.model.streamGenerateText( contents );

		return processCandidatesStreamToContent(
			candidatesGenerator,
			( responseContent ) => {
				this.history = [ ...this.history, newContent, responseContent ];
			}
		);
	}
}

/**
 * Processes a stream of candidates and yields their first content's chunks.
 *
 * Other than yielding the content chunks, this generator function aggregates the candidates chunks into a single
 * candidates instance, and once completed invokes the provided callback with the complete response content.
 *
 * @since n.e.x.t
 *
 * @param {Object}   candidatesGenerator The generator that yields the chunks of response candidates.
 * @param {Function} completeCallback    Callback that is called with the complete response content.
 * @return {Object} The generator that yields chunks of the response content.
 */
async function* processCandidatesStreamToContent(
	candidatesGenerator,
	completeCallback
) {
	const candidatesProcessor = processCandidatesStream( candidatesGenerator );
	for await ( const candidates of candidatesGenerator ) {
		candidatesProcessor.addChunk( candidates );

		// TODO: Support optional candidateFilterArgs, similar to PHP implementation.
		const partialContents = getCandidateContents( candidates );
		const partialContent = partialContents[ 0 ];

		yield partialContent;
	}

	const completeCandidates = candidatesProcessor.getComplete();

	// TODO: Support optional candidateFilterArgs, similar to PHP implementation.
	const completeContents = getCandidateContents( completeCandidates );
	const completeContent = completeContents[ 0 ];

	completeCallback( completeContent );
}
