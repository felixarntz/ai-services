/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import * as enums from '../enums';
import { textToContent } from '../helpers';
import { validateContent, validateCapabilities } from '../util';

/**
 * Gets the text from a content object.
 *
 * While the API allows for an array of multiple content objects to be passed, this is not supported by the browser
 * implementation. As such, this function will throw an error if it encounters an array of multiple content objects.
 *
 * @since 0.3.0
 *
 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional history.
 * @return {string|Object|Object[]} The content, as a string, content object, or array of content objects.
 */
function prepareContentForBrowser( content ) {
	if ( typeof content === 'string' ) {
		return content;
	}

	// If an array is passed, it's either parts (i.e. a single prompt) or history.
	if ( Array.isArray( content ) ) {
		if (
			( content[ 0 ].role || content[ 0 ].parts ) &&
			content.length > 1
		) {
			throw new Error(
				'The browser service does not support history at this time.'
			);
		}

		let parts;
		if ( content[ 0 ].role || content[ 0 ].parts ) {
			parts = content[ 0 ].parts;
		} else {
			parts = content;
		}
		return parts.map( ( part ) => part.text || '' ).join( '\n' );
	}

	if ( typeof content === 'object' ) {
		return content.parts.map( ( part ) => part.text || '' ).join( '\n' );
	}

	throw new Error( 'Invalid content format.' );
}

/**
 * Creates a new session with the browser model, based on supported model params.
 *
 * See https://github.com/explainers-by-googlers/prompt-api#examples for supported parameters.
 *
 * @since 0.3.0
 * @since 0.4.0 Checks for newer `ai.languageModel` property.
 *
 * @param {Object} modelParams Model parameters.
 * @return {Promise<Object>} The browser session.
 */
async function createSession( modelParams ) {
	const browserParams = {};
	if ( modelParams.generationConfig ) {
		if ( modelParams.generationConfig.temperature ) {
			browserParams.temperature =
				modelParams.generationConfig.temperature;
		}
		if ( modelParams.generationConfig.topK ) {
			browserParams.topK = modelParams.generationConfig.topK;
		}
	}
	if ( modelParams.systemInstruction ) {
		if ( typeof modelParams.systemInstruction === 'string' ) {
			browserParams.systemInstruction = modelParams.systemInstruction;
		} else if ( modelParams.systemInstruction.parts?.[ 0 ]?.text ) {
			browserParams.systemInstruction =
				modelParams.systemInstruction.parts[ 0 ].text;
		}
	}

	const llm = window.ai.languageModel || window.ai.assistant;

	if ( Object.keys( browserParams ).length === 0 ) {
		return llm.create();
	}

	try {
		const session = await llm.create( browserParams );
		return session;
	} catch ( error ) {
		// eslint-disable-next-line no-console
		console.warn(
			'Failed to create browser session with modelParams, therefore creating default session. Original error:',
			error
		);
		return llm.create();
	}
}

/**
 * Wraps the browser text generator to match the candidates API syntax.
 *
 * @since 0.3.0
 *
 * @param {Object} resultTextGenerator The browser text generator.
 * @return {Object} The wrapped generator.
 */
async function* wrapBrowserTextGenerator( resultTextGenerator ) {
	/*
	 * The browser implementation currently yields the entire text generated so far for every chunk,
	 * so we need to calculate the new chunk.
	 */
	let textProcessed = '';
	for await ( const resultText of resultTextGenerator ) {
		const chunk = resultText.substring( textProcessed.length );
		textProcessed = resultText;

		yield [
			{
				content: textToContent( chunk, enums.ContentRole.MODEL ),
			},
		];
	}
}

/**
 * Special model class only used for models of the 'browser' service.
 *
 * @since 0.3.0
 */
export default class BrowserGenerativeAiModel extends GenerativeAiModel {
	/**
	 * Generates text content using the model.
	 *
	 * @since 0.3.0
	 *
	 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional
	 *                                         history.
	 * @return {Promise<Object[]>} Model response candidates with the generated text content.
	 */
	async generateText( content ) {
		validateCapabilities( this.capabilities, [
			enums.AiCapability.TEXT_GENERATION,
		] );

		// Do some very basic validation.
		validateContent( content );

		const session = await createSession( this.modelParams );
		const text = prepareContentForBrowser( content );
		const resultText = await session.prompt( text );

		// Normalize result shape to match candidates API syntax from other services.
		return [
			{
				content: textToContent( resultText, enums.ContentRole.MODEL ),
			},
		];
	}

	/**
	 * Generates text content using the model, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional
	 *                                         history.
	 * @return {Promise<Object>} The generator that yields chunks of response candidates with the generated text
	 *                           content.
	 */
	async streamGenerateText( content ) {
		validateCapabilities( this.capabilities, [
			enums.AiCapability.TEXT_GENERATION,
		] );

		// Do some very basic validation.
		validateContent( content );

		const session = await createSession( this.modelParams );
		const text = prepareContentForBrowser( content );
		const resultTextGenerator = await session.promptStreaming( text );

		// Normalize result shape to match candidates API syntax from other services.
		return wrapBrowserTextGenerator( resultTextGenerator );
	}
}
