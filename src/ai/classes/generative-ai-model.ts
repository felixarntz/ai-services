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
import type {
	ModelMetadata,
	ModelParams,
	Content,
	Part,
	Candidates,
	AsyncCandidatesGenerator,
} from '../types';

const EMPTY_OBJECT = {};

type ModelData = {
	serviceSlug: string;
	metadata: ModelMetadata;
};

/**
 * Model class.
 *
 * @since 0.3.0
 */
export default class GenerativeAiModel {
	serviceSlug: string;
	metadata: ModelMetadata;
	modelParams: ModelParams;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param model       - Model object.
	 * @param modelParams - Model parameters passed. At a minimum this must include the unique "feature" identifier.
	 */
	constructor( model: ModelData, modelParams: ModelParams ) {
		validateModelParams( modelParams );

		this.serviceSlug = model.serviceSlug;
		this.metadata = model.metadata;
		this.modelParams = modelParams || EMPTY_OBJECT;
	}

	/**
	 * Gets the model slug.
	 *
	 * @since 0.3.0
	 *
	 * @returns Model slug.
	 */
	getModelSlug() {
		/*
		 * Note: The actual model selection happens on the server.
		 * The client-side GenerativeAiService class still attempts to find a suitable model based on the model slug or
		 * capabilities, but it is only done to provide parity with the server-side API.
		 */
		return this.metadata.slug;
	}

	/**
	 * Gets the model metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @returns Model metadata.
	 */
	getModelMetadata() {
		return this.metadata;
	}

	/**
	 * Generates text content using the model.
	 *
	 * @since 0.3.0
	 *
	 * @param content - Content data to pass to the model, including the prompt and optional history.
	 * @returns Model response candidates with the generated text content.
	 */
	async generateText(
		content: string | Part[] | Content | Content[]
	): Promise< Candidates > {
		validateCapabilities( this.metadata.capabilities, [
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
			throw new Error(
				error instanceof Error ? error.message : String( error )
			);
		}
	}

	/**
	 * Generates text content using the model, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param content - Content data to pass to the model, including the prompt and optional history.
	 * @returns The generator that yields chunks of response candidates with the generated text content.
	 */
	async streamGenerateText(
		content: string | Part[] | Content | Content[]
	): Promise< AsyncCandidatesGenerator > {
		validateCapabilities( this.metadata.capabilities, [
			enums.AiCapability.TEXT_GENERATION,
		] );

		const modelParams = { ...this.modelParams };

		// Do some very basic validation.
		validateContent( content );

		const response: Response = await apiFetch( {
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
	 * @param history - Chat history.
	 * @returns Chat session.
	 */
	startChat( history: Content[] ): ChatSession {
		validateCapabilities( this.metadata.capabilities, [
			enums.AiCapability.TEXT_GENERATION,
			enums.AiCapability.CHAT_HISTORY,
		] );

		return new ChatSession( this, { history } );
	}

	/**
	 * Generates an image using the model.
	 *
	 * @since 0.5.0
	 *
	 * @param content - Content data to pass to the model, including the prompt and optional history.
	 * @returns Model response candidates with the generated image.
	 */
	async generateImage(
		content: string | Part[] | Content | Content[]
	): Promise< Candidates > {
		validateCapabilities( this.metadata.capabilities, [
			enums.AiCapability.IMAGE_GENERATION,
		] );

		const modelParams = { ...this.modelParams };

		// Do some very basic validation.
		validateContent( content );

		try {
			return await apiFetch( {
				path: `/ai-services/v1/services/${ this.serviceSlug }:generate-image`,
				method: 'POST',
				data: {
					content,
					modelParams,
				},
			} );
		} catch ( error ) {
			throw new Error(
				error instanceof Error ? error.message : String( error )
			);
		}
	}
}
