/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import ChatSession from './chat-session';
import * as enums from '../enums';
import { detectRequestedCapabilitiesFromContent, findModel } from '../util';

const EMPTY_OBJECT = {};

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
	 * @param {Object} service                  Service object.
	 * @param {Object} service.metadata         Service metadata.
	 * @param {Object} service.available_models Metadata for each model, mapped by model slug.
	 */
	constructor( { metadata, available_models: models } ) {
		if ( ! models || ! Object.keys( models ).length ) {
			throw new Error(
				`No models available for the service ${ metadata.slug }. Is it available?`
			);
		}

		this.metadata = metadata;
		this.models = models;
	}

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return {string} Service slug.
	 */
	getServiceSlug() {
		return this.metadata.slug;
	}

	/**
	 * Gets the service metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @return {string} Service metadata.
	 */
	getServiceMetadata() {
		return this.metadata;
	}

	/**
	 * Lists the available generative model slugs and their capabilities.
	 *
	 * @since 0.1.0
	 *
	 * @return {Object} Metadata for each model, mapped by model slug.
	 */
	listModels() {
		return this.models;
	}

	/**
	 * Gets a generative model instance from the service.
	 *
	 * @since 0.3.0
	 *
	 * @param {Object} modelParams Model parameters. At a minimum this must include the unique "feature" identifier. It
	 *                             can also include the model slug and other optional parameters.
	 * @return {GenerativeAiModel} Generative AI model instance.
	 */
	getModel( modelParams ) {
		modelParams = modelParams || EMPTY_OBJECT;

		const model = findModel( this.models, modelParams );

		return new GenerativeAiModel(
			{
				serviceSlug: this.metadata.slug,
				...model,
			},
			modelParams
		);
	}

	/**
	 * Generates text content using the service.
	 *
	 * This is a short-hand method for `service.getModel( modelParams ).generateText( content )`.
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
		// The `enums.AiCapability.TEXT_GENERATION` capability is naturally implied to generate text.
		if ( ! modelParams?.capabilities ) {
			modelParams = {
				...modelParams,
				capabilities: detectRequestedCapabilitiesFromContent( content, [
					enums.AiCapability.TEXT_GENERATION,
				] ),
			};
		}

		const model = this.getModel( modelParams );
		return model.generateText( content );
	}

	/**
	 * Generates text content using the service, streaming the response.
	 *
	 * This is a short-hand method for `service.getModel( modelParams ).streamGenerateText( content )`.
	 *
	 * @since 0.3.0
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
		/*
		 * The `enums.AiCapability.TEXT_GENERATION` capability is naturally implied to generate text.
		 * And in case a history is provided, we also need `enums.AiCapability.CHAT_HISTORY`.
		 */
		if ( ! modelParams?.capabilities ) {
			modelParams = {
				...modelParams,
				capabilities: detectRequestedCapabilitiesFromContent( content, [
					enums.AiCapability.TEXT_GENERATION,
				] ),
			};
		}

		const model = this.getModel( modelParams );
		return model.streamGenerateText( content );
	}

	/**
	 * Starts a multi-turn chat session using the service.
	 *
	 * This is a short-hand method for `service.getModel( modelParams ).startChat( history )`.
	 *
	 * @since 0.1.0
	 *
	 * @param {Object[]} history     Chat history.
	 * @param {Object}   modelParams Model parameters.
	 * @return {ChatSession} Chat session.
	 */
	startChat( history, modelParams ) {
		/*
		 * The `enums.AiCapability.TEXT_GENERATION` capability is naturally implied to generate text.
		 * And for chat, we also need `enums.AiCapability.CHAT_HISTORY`.
		 */
		if ( ! modelParams?.capabilities ) {
			modelParams = {
				...modelParams,
				capabilities: [
					enums.AiCapability.TEXT_GENERATION,
					enums.AiCapability.CHAT_HISTORY,
				],
			};
		}

		const model = this.getModel( modelParams );
		return model.startChat( history );
	}

	/**
	 * Generates an image using the service.
	 *
	 * This is a short-hand method for `service.getModel( modelParams ).generateImage( content )`.
	 *
	 * @since 0.5.0
	 *
	 * @param {string|Object|Object[]} content     Content data to pass to the model, including the prompt and optional
	 *                                             history.
	 * @param {Object}                 modelParams Model parameters. At a minimum this must include the unique
	 *                                             "feature" identifier. It can also include the model slug and other
	 *                                             optional parameters.
	 * @return {Promise<Object[]>} Model response candidates with the generated image.
	 */
	async generateImage( content, modelParams ) {
		// The `enums.AiCapability.IMAGE_GENERATION` capability is naturally implied to generate text.
		if ( ! modelParams?.capabilities ) {
			modelParams = {
				...modelParams,
				capabilities: detectRequestedCapabilitiesFromContent( content, [
					enums.AiCapability.IMAGE_GENERATION,
				] ),
			};
		}

		const model = this.getModel( modelParams );
		return model.generateImage( content );
	}
}
