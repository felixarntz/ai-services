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
 * @since n.e.x.t
 *
 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional history.
 * @return {string} The text content.
 */
function getTextFromContent( content ) {
	if ( typeof content === 'string' ) {
		return content;
	}

	// If an array is passed, it's either parts (i.e. a single prompt) or history.
	if ( Array.isArray( content ) ) {
		let parts;
		if (
			( content[ 0 ].role || content[ 0 ].parts ) &&
			content.length > 1
		) {
			throw new Error(
				'The browser service does not support history at this time.'
			);
		} else if ( content[ 0 ].role || content[ 0 ].parts ) {
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
 * Wraps the browser text generator to match the candidates API syntax.
 *
 * @since n.e.x.t
 *
 * @param {Object} resultTextGenerator The browser text generator.
 * @return {Object} The wrapped generator.
 */
async function* wrapBrowserTextGenerator( resultTextGenerator ) {
	for await ( const resultText of resultTextGenerator ) {
		yield [
			{
				content: textToContent( resultText, enums.ContentRole.MODEL ),
			},
		];
	}
}

/**
 * Special model class only used for models of the 'browser' service.
 *
 * @since n.e.x.t
 */
export default class BrowserGenerativeAiModel extends GenerativeAiModel {
	/**
	 * Generates text content using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional
	 *                                         history.
	 * @return {Promise<Object[]>} Model response candidates with the generated text content.
	 */
	async generateText( content ) {
		validateCapabilities( this.capabilities, [
			enums.AiCapability.TEXT_GENERATION,
		] );

		const modelParams = { ...this.modelParams };

		// Do some very basic validation.
		validateContent( content );

		const text = getTextFromContent( content );

		const session = await window.ai.assistant.create( modelParams );
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
	 * @since n.e.x.t
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

		const modelParams = { ...this.modelParams };

		// Do some very basic validation.
		validateContent( content );

		const text = getTextFromContent( content );

		const session = await window.ai.assistant.create( modelParams );
		const resultTextGenerator = await session.promptStreaming( text );

		// Normalize result shape to match candidates API syntax from other services.
		return wrapBrowserTextGenerator( resultTextGenerator );
	}
}
