/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import GenerativeAiService from './generative-ai-service';
import * as enums from '../enums';
import { validateContent } from '../util';

/**
 * Special service class only used for the 'browser' service.
 *
 * @since 0.1.0
 */
export default class BrowserGenerativeAiService extends GenerativeAiService {
	/**
	 * Generates text content using the service.
	 *
	 * @since 0.1.0
	 *
	 * @param {string|Object|Object[]} content     Content data to pass to the model, including the prompt and optional
	 *                                             history.
	 * @param {Object}                 modelParams Model parameters. At a minimum this must include the unique
	 *                                             "feature" identifier. It can also include the model slug and other
	 *                                             optional parameters.
	 * @return {Promise<Object[]>} Model response candidates with the generated text content.
	 */
	async generateText( content, modelParams ) {
		if (
			! this.capabilities.includes( enums.AiCapability.TEXT_GENERATION )
		) {
			throw new Error(
				__(
					'The service does not support text generation.',
					'ai-services'
				)
			);
		}

		if ( ! modelParams?.feature ) {
			throw new Error(
				__(
					'You must provide a "feature" identifier as part of the model parameters, which only contains lowercase letters, numbers, and hyphens.',
					'ai-services'
				)
			);
		}

		// Do some very basic validation.
		validateContent( content );

		if ( typeof content !== 'string' ) {
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
				content = parts.map( ( part ) => part.text || '' ).join( '\n' );
			} else if ( typeof content === 'object' ) {
				content = content.parts
					.map( ( part ) => part.text || '' )
					.join( '\n' );
			}
		}

		const session = await window.ai.assistant.create( modelParams );
		const resultText = await session.prompt( content );

		// Normalize result shape to match candidates API syntax from other services.
		return [
			{
				content: {
					role: enums.ContentRole.MODEL,
					parts: [ { text: resultText } ],
				},
			},
		];
	}

	/**
	 * Generates text content using the service, streaming the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param {string|Object|Object[]} content     Content data to pass to the model, including the prompt and optional
	 *                                             history.
	 * @param {Object}                 modelParams Model parameters. At a minimum this must include the unique
	 *                                             "feature" identifier. It can also include the model slug and other
	 *                                             optional parameters.
	 * @return {Promise<Object>} The generator that yields chunks of response candidates with the generated text
	 *                           content.
	 */
	async streamGenerateText( content, modelParams ) {
		if (
			! this.capabilities.includes( enums.AiCapability.TEXT_GENERATION )
		) {
			throw new Error(
				__(
					'The service does not support text generation.',
					'ai-services'
				)
			);
		}

		if ( ! modelParams?.feature ) {
			throw new Error(
				__(
					'You must provide a "feature" identifier as part of the model parameters, which only contains lowercase letters, numbers, and hyphens.',
					'ai-services'
				)
			);
		}

		// Do some very basic validation.
		validateContent( content );

		if ( typeof content !== 'string' ) {
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
				content = parts.map( ( part ) => part.text || '' ).join( '\n' );
			} else if ( typeof content === 'object' ) {
				content = content.parts
					.map( ( part ) => part.text || '' )
					.join( '\n' );
			}
		}

		const session = await window.ai.assistant.create( modelParams );
		const resultTextGenerator = await session.promptStreaming( content );

		// Normalize result shape to match candidates API syntax from other services.
		return wrapBrowserTextGenerator( resultTextGenerator );
	}
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
				content: {
					role: enums.ContentRole.MODEL,
					parts: [ { text: resultText } ],
				},
			},
		];
	}
}
