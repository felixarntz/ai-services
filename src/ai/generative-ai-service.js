/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getCandidateContents } from './helpers';
import * as enums from './enums';
import { formatNewContent } from './util';
import processStream from '../utils/process-stream';

/**
 * Validates the chat history.
 *
 * @since 0.1.0
 *
 * @param {Object[]} history Chat history.
 */
function validateChatHistory( history ) {
	history.forEach( ( content, index ) => {
		if ( ! content.role || ! content.parts ) {
			throw new Error(
				__(
					'The content object must have a role and parts properties.',
					'ai-services'
				)
			);
		}

		if ( index === 0 && content.role !== enums.ContentRole.USER ) {
			throw new Error(
				__(
					'The first content object in the history must be user content.',
					'ai-services'
				)
			);
		}

		if ( content.parts.length === 0 ) {
			throw new Error(
				__(
					'Each Content instance must have at least one part.',
					'ai-services'
				)
			);
		}
	} );
}

/**
 * Performs some very basic client-side validation for the content argument.
 *
 * @since 0.1.0
 *
 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional history.
 */
function validateContent( content ) {
	if ( ! content ) {
		throw new Error(
			__(
				'The content argument is required to generate content.',
				'ai-services'
			)
		);
	}
	if ( ! Array.isArray( content ) ) {
		if ( typeof content === 'object' ) {
			if ( ! content.role || ! content.parts ) {
				throw new Error(
					__(
						'The content object must have a role and parts properties.',
						'ai-services'
					)
				);
			}
		} else if ( typeof content !== 'string' ) {
			throw new Error(
				__(
					'The content argument must be a string, an object, or an array of objects.',
					'ai-services'
				)
			);
		}
	}
}

/**
 * Service class.
 *
 * @since 0.1.0
 */
class GenerativeAiService {
	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param {Object}   service                  Service object.
	 * @param {string}   service.slug             Service slug.
	 * @param {string}   service.name             Service name.
	 * @param {string[]} service.capabilities     AI capabilities that the service supports.
	 * @param {Object}   service.available_models Map of the available model slugs and their capabilities.
	 */
	constructor( { slug, name, capabilities, available_models: models } ) {
		if ( ! models || ! Object.keys( models ).length ) {
			throw new Error(
				`No models available for the service ${ slug }. Is it available?`
			);
		}

		this.slug = slug;
		this.name = name;
		this.capabilities = capabilities;
		this.models = models;
	}

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return {string} Service name.
	 */
	getServiceSlug() {
		return this.slug;
	}

	/**
	 * Gets the list of AI capabilities that the service and its models support.
	 *
	 * @since 0.1.0
	 *
	 * @return {string[]} The list of AI capabilities.
	 */
	getCapabilities() {
		return this.capabilities;
	}

	/**
	 * Lists the available generative model slugs and their capabilities.
	 *
	 * @since 0.1.0
	 *
	 * @return {Object} Map of the available model slugs and their capabilities.
	 */
	listModels() {
		return this.models;
	}

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

		// The `enums.AiCapability.TEXT_GENERATION` capability is naturally implied to generate text.
		if ( ! modelParams?.capabilities ) {
			modelParams = {
				...modelParams,
				capabilities: [ enums.AiCapability.TEXT_GENERATION ],
			};
		}

		// Do some very basic validation.
		validateContent( content );

		try {
			return await apiFetch( {
				path: `/ai-services/v1/services/${ this.slug }:generate-text`,
				method: 'POST',
				data: {
					content,
					modelParams: modelParams || {},
				},
			} );
		} catch ( error ) {
			throw new Error( error.message || error.code || error );
		}
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

		// The `enums.AiCapability.TEXT_GENERATION` capability is naturally implied to generate text.
		if ( ! modelParams?.capabilities ) {
			modelParams = {
				...modelParams,
				capabilities: [ enums.AiCapability.TEXT_GENERATION ],
			};
		}

		// Do some very basic validation.
		validateContent( content );

		const response = await apiFetch( {
			path: `/ai-services/v1/services/${ this.slug }:stream-generate-text`,
			method: 'POST',
			data: {
				content,
				modelParams: modelParams || {},
			},
			headers: {
				Accept: 'text/event-stream',
			},
			parse: false,
		} );

		return processStream( response );
	}

	/**
	 * Starts a multi-turn chat session using the service.
	 *
	 * @since 0.1.0
	 *
	 * @param {Object[]} history     Chat history.
	 * @param {Object}   modelParams Model parameters.
	 * @return {ChatSession} Chat session.
	 */
	startChat( history, modelParams ) {
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

		return new ChatSession( this, { history, modelParams } );
	}
}

/**
 * Special service class only used for the 'browser' service.
 *
 * @since 0.1.0
 */
class BrowserGenerativeAiService extends GenerativeAiService {
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

/**
 * Class representing a chat session with a generative model.
 *
 * @since 0.1.0
 */
export class ChatSession {
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

const services = {};

/**
 * Gets the generative AI service instance for the given service data.
 *
 * The service data must be an object received from the services REST endpoint.
 *
 * @since 0.1.0
 *
 * @param {Object} serviceData                  Service data.
 * @param {string} serviceData.slug             Service slug.
 * @param {string} serviceData.name             Service name.
 * @param {Object} serviceData.available_models Map of the available model slugs and their capabilities.
 * @return {GenerativeAiService} Generative AI service instance.
 */
export function getGenerativeAiService( serviceData ) {
	if ( ! services[ serviceData.slug ] ) {
		if ( serviceData.slug === 'browser' ) {
			services[ serviceData.slug ] = new BrowserGenerativeAiService(
				serviceData
			);
		} else {
			services[ serviceData.slug ] = new GenerativeAiService(
				serviceData
			);
		}
	}

	return services[ serviceData.slug ];
}
