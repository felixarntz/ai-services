/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import { getCandidateContents, processCandidatesStream } from '../helpers';
import { validateChatHistory, formatNewContent } from '../util';
import type {
	Content,
	Part,
	AsyncCandidatesGenerator,
	ChatSessionOptions,
} from '../types';

/**
 * Class representing a chat session with a generative model.
 *
 * @since 0.1.0
 */
export default class ChatSession {
	model: GenerativeAiModel;
	history: Content[];

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param model   - Generative AI model.
	 * @param options - Chat options.
	 */
	constructor( model: GenerativeAiModel, options: ChatSessionOptions ) {
		const { history } = options;

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
	 * @returns Chat history.
	 */
	getHistory(): Content[] {
		return this.history;
	}

	/**
	 * Sends a chat message to the model.
	 *
	 * @since 0.1.0
	 *
	 * @param content - Chat message content.
	 * @returns The response content.
	 */
	async sendMessage(
		content: string | Part[] | Content
	): Promise< Content > {
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
	 * @since 0.3.0
	 *
	 * @param content - Chat message content.
	 * @returns The generator that yields chunks of the response content.
	 */
	async streamSendMessage(
		content: string | Part[] | Content
	): Promise< AsyncGenerator< Content, void, void > > {
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
 * @since 0.3.0
 *
 * @param candidatesGenerator - The generator that yields the chunks of response candidates.
 * @param completeCallback    - Callback that is called with the complete response content.
 * @returns The generator that yields chunks of the response content.
 */
async function* processCandidatesStreamToContent(
	candidatesGenerator: AsyncCandidatesGenerator,
	completeCallback: ( content: Content ) => void
): AsyncGenerator< Content, void, void > {
	const candidatesProcessor = processCandidatesStream( candidatesGenerator );
	for await ( const candidates of candidatesGenerator ) {
		candidatesProcessor.addChunk( candidates );

		// TODO: Support optional candidateFilterArgs, similar to PHP implementation.
		const partialContents = getCandidateContents( candidates );
		const partialContent = partialContents[ 0 ];

		yield partialContent;
	}

	const completeCandidates = candidatesProcessor.getComplete();

	if ( ! completeCandidates ) {
		throw new Error(
			'Candidates stream is empty. No content was generated.'
		);
	}

	// TODO: Support optional candidateFilterArgs, similar to PHP implementation.
	const completeContents = getCandidateContents( completeCandidates );
	const completeContent = completeContents[ 0 ];

	completeCallback( completeContent );
}
