/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import ChatSession from './chat-session';
import * as enums from '../enums';
import { detectRequestedCapabilitiesFromContent, findModel } from '../util';
import type {
	ServiceResource,
	ServiceMetadata,
	ModelMetadata,
	ModelParams,
	Content,
	Part,
	Candidates,
	AsyncCandidatesGenerator,
} from '../types';

const EMPTY_OBJECT = {};

/**
 * Service class.
 *
 * @since 0.1.0
 */
export default class GenerativeAiService {
	metadata: ServiceMetadata;
	models: Record< string, ModelMetadata >;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param service - Service object.
	 */
	constructor( service: ServiceResource ) {
		const { metadata, available_models: models } = service;

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
	 * @returns Service slug.
	 */
	getServiceSlug() {
		return this.metadata.slug;
	}

	/**
	 * Gets the service metadata.
	 *
	 * @since 0.7.0
	 *
	 * @returns Service metadata.
	 */
	getServiceMetadata() {
		return this.metadata;
	}

	/**
	 * Lists the available generative model slugs and their capabilities.
	 *
	 * @since 0.1.0
	 *
	 * @returns Metadata for each model, mapped by model slug.
	 */
	listModels() {
		return this.models;
	}

	/**
	 * Gets a generative model instance from the service.
	 *
	 * @since 0.3.0
	 *
	 * @param modelParams - Model parameters. At a minimum this must include the unique "feature" identifier.
	 * @returns Generative AI model instance.
	 */
	getModel( modelParams: ModelParams ): GenerativeAiModel {
		modelParams = modelParams || EMPTY_OBJECT;

		const model = findModel( this.models, modelParams );

		return new GenerativeAiModel(
			{
				serviceSlug: this.metadata.slug,
				metadata: { ...model },
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
	 * @param content     - Content data to pass to the model, including the prompt and optional history.
	 * @param modelParams - Model parameters. At a minimum this must include the unique "feature" identifier.
	 * @returns Model response candidates with the generated text content.
	 */
	async generateText(
		content: string | Part[] | Content | Content[],
		modelParams: ModelParams
	): Promise< Candidates > {
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
	 * @param content     - Content data to pass to the model, including the prompt and optional history.
	 * @param modelParams - Model parameters. At a minimum this must include the unique "feature" identifier.
	 * @returns The generator that yields chunks of response candidates with the generated text content.
	 */
	async streamGenerateText(
		content: string | Part[] | Content | Content[],
		modelParams: ModelParams
	): Promise< AsyncCandidatesGenerator > {
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
	 * @param history     - Chat history.
	 * @param modelParams - Model parameters.
	 * @returns Chat session.
	 */
	startChat( history: Content[], modelParams: ModelParams ): ChatSession {
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
	 * @param content     - Content data to pass to the model, including the prompt and optional history.
	 * @param modelParams - Model parameters. At a minimum this must include the unique "feature" identifier.
	 * @returns Model response candidates with the generated image.
	 */
	async generateImage(
		content: string | Part[] | Content | Content[],
		modelParams: ModelParams
	): Promise< Candidates > {
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

	/**
	 * Transforms text to speech using the service.
	 *
	 * This is a short-hand method for `service.getModel( modelParams ).textToSpeech( content )`.
	 *
	 * @since 0.7.0
	 *
	 * @param content     - The content to transform to speech.
	 * @param modelParams - Model parameters. At a minimum this must include the unique "feature" identifier.
	 * @returns Model response candidates with the generated speech.
	 */
	async textToSpeech(
		content: string | Part[] | Content | Content[],
		modelParams: ModelParams
	): Promise< Candidates > {
		// The `enums.AiCapability.TEXT_TO_SPEECH` capability is naturally implied to generate speech.
		if ( ! modelParams?.capabilities ) {
			modelParams = {
				...modelParams,
				capabilities: detectRequestedCapabilitiesFromContent( content, [
					enums.AiCapability.TEXT_TO_SPEECH,
				] ),
			};
		}

		const model = this.getModel( modelParams );
		return model.textToSpeech( content );
	}
}
