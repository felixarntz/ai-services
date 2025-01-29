/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import ChatSession from './chat-session';
import * as enums from '../enums';
import {
	validateContent,
	validateModelParams,
	validateCapabilities,
} from '../util';
import processStream from '../../utils/process-stream';

const EMPTY_OBJECT = {};

/**
 * Model class.
 *
 * @since 0.3.0
 */
export default class GenerativeAiModel {
	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param {Object}   model              Model object.
	 * @param {string}   model.serviceSlug  Service slug.
	 * @param {string}   model.slug         Model slug.
	 * @param {string}   model.name         Model name.
	 * @param {string[]} model.capabilities AI capabilities that the model supports.
	 * @param {Object}   modelParams        Model parameters passed. At a minimum this must include the unique
	 *                                      "feature" identifier. It can also include the model slug and other optional
	 *                                      parameters.
	 */
	constructor( { serviceSlug, slug, name, capabilities }, modelParams ) {
		validateModelParams( modelParams );

		this.serviceSlug = serviceSlug;
		this.slug = slug;
		this.name = name;
		this.capabilities = capabilities;
		this.modelParams = modelParams || EMPTY_OBJECT;
	}

	/**
	 * Gets the model slug.
	 *
	 * @since 0.3.0
	 *
	 * @return {string} Model name.
	 */
	getModelSlug() {
		/*
		 * Note: The actual model selection happens on the server.
		 * The client-side GenerativeAiService class still attempts to find a suitable model based on the model slug or
		 * capabilities, but it is only done to provide parity with the server-side API.
		 */
		return this.slug;
	}

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

		const modelParams = { ...this.modelParams };

		// Do some very basic validation.
		validateContent( content );

		try {
			return await apiFetch( {
				path: `/ai-services/v1/services/${ this.serviceSlug }:generate-text`,
				method: 'POST',
				data: {
					content,
					modelParams,
				},
			} );
		} catch ( error ) {
			throw new Error( error.message || error.code || error );
		}
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

		const modelParams = { ...this.modelParams };

		// Do some very basic validation.
		validateContent( content );

		const response = await apiFetch( {
			path: `/ai-services/v1/services/${ this.serviceSlug }:stream-generate-text`,
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
	 * Starts a multi-turn chat session using the model.
	 *
	 * @since 0.3.0
	 *
	 * @param {Object[]} history Chat history.
	 * @return {ChatSession} Chat session.
	 */
	startChat( history ) {
		validateCapabilities( this.capabilities, [
			enums.AiCapability.TEXT_GENERATION,
			enums.AiCapability.CHAT_HISTORY,
		] );

		return new ChatSession( this, { history } );
	}
}
