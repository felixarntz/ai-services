/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import ChatSession from './chat-session';
import * as enums from '../enums';
import { validateContent } from '../util';
import processStream from '../../utils/process-stream';

/**
 * Service class.
 *
 * @since 0.1.0
 */
export default class GenerativeAiService {
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
