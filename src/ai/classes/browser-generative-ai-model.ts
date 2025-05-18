/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import * as enums from '../enums';
import { textToContent } from '../helpers';
import { validateContent, validateCapabilities } from '../util';
import { getResponseGenerator } from '../../utils/process-stream';
import {
	Content,
	Part,
	Candidates,
	AsyncCandidatesGenerator,
	ModelParams,
	TextGenerationConfig,
} from '../types';

/*
 * Using `@types/dom-chromium-ai` does not properly work, so we'll redefine the relevant types here.
 * See https://github.com/DefinitelyTyped/DefinitelyTyped/blob/master/types/dom-chromium-ai/index.d.ts
 */
interface AILanguageModelFactory {
	create: (
		options?: AILanguageModelCreateOptions
	) => Promise< AILanguageModel >;
	capabilities: () => Promise< AILanguageModelCapabilities >;
}
interface AILanguageModelCreateOptions {
	topK?: number;
	temperature?: number;
	systemPrompt?: string;
	initialPrompts?: AILanguageModelPrompt[];
}
type AILanguageModelPromptRole = 'user' | 'assistant';
interface AILanguageModelPrompt {
	role: AILanguageModelPromptRole;
	content: string;
}
interface AILanguageModel {
	prompt: (
		input: AILanguageModelPromptInput,
		options?: AILanguageModelPromptOptions
	) => Promise< string >;
	promptStreaming: (
		input: AILanguageModelPromptInput,
		options?: AILanguageModelPromptOptions
	) => ReadableStream< string >;
}
type AILanguageModelPromptInput =
	| string
	| AILanguageModelPrompt
	| AILanguageModelPrompt[];
interface AILanguageModelPromptOptions {
	signal?: AbortSignal;
}
interface AILanguageModelCapabilities {
	readonly available: AICapabilityAvailability;
}
type AICapabilityAvailability = 'readily' | 'after-download' | 'no';

/**
 * Gets the text from a content object.
 *
 * While the API allows for an array of multiple content objects to be passed, this is not supported by the browser
 * implementation. As such, this function will throw an error if it encounters an array of multiple content objects.
 *
 * @since 0.3.0
 *
 * @param content - Content data to pass to the model, including the prompt and optional history.
 * @returns The content as a string.
 */
function prepareContentForBrowser(
	content: string | Part[] | Content | Content[]
): string {
	if ( typeof content === 'string' ) {
		return content;
	}

	// If an array is passed, it's either parts (i.e. a single prompt) or history.
	if ( Array.isArray( content ) ) {
		if (
			'role' in content[ 0 ] &&
			'parts' in content[ 0 ] &&
			content.length > 1
		) {
			throw new Error(
				'The browser service does not support history at this time.'
			);
		}

		let parts: Part[];
		if ( 'role' in content[ 0 ] && 'parts' in content[ 0 ] ) {
			parts = content[ 0 ].parts;
		} else {
			// Assuming it's an array of Part if not an array of Content.
			parts = content as Part[];
		}
		return parts
			.map( ( part ) => ( 'text' in part ? part.text : '' ) )
			.filter( Boolean )
			.join( '\n' );
	}

	if ( typeof content === 'object' ) {
		return content.parts
			.map( ( part ) => ( 'text' in part ? part.text : '' ) )
			.join( '\n' );
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
 * @since 0.6.0 Checks for newer `LanguageModel` property.
 *
 * @param modelParams - Model parameters.
 * @returns The browser session.
 */
async function createSession(
	modelParams: ModelParams
): Promise< AILanguageModel > {
	const browserParams: AILanguageModelCreateOptions = {};
	if (
		modelParams.generationConfig &&
		( 'temperature' in modelParams.generationConfig ||
			'topK' in modelParams.generationConfig )
	) {
		const generationConfig =
			modelParams.generationConfig as TextGenerationConfig;
		if ( generationConfig.temperature ) {
			browserParams.temperature = generationConfig.temperature;
		}
		if ( generationConfig.topK ) {
			browserParams.topK = generationConfig.topK;
		}
	}
	if ( modelParams.systemInstruction ) {
		if ( typeof modelParams.systemInstruction === 'string' ) {
			browserParams.systemPrompt = modelParams.systemInstruction;
		} else if (
			modelParams.systemInstruction.parts.length &&
			'text' in modelParams.systemInstruction.parts[ 0 ]
		) {
			browserParams.systemPrompt =
				modelParams.systemInstruction.parts[ 0 ].text;
		}
	}

	let llm: AILanguageModelFactory | undefined;
	if ( 'LanguageModel' in window ) {
		llm = window.LanguageModel as AILanguageModelFactory;
	} else if (
		'ai' in window &&
		typeof window.ai === 'object' &&
		window.ai !== null &&
		'languageModel' in window.ai
	) {
		llm = window.ai.languageModel as AILanguageModelFactory;
	}

	if ( ! llm ) {
		throw new Error( 'Browser AI capabilities not available.' );
	}

	if ( Object.keys( browserParams ).length === 0 ) {
		return llm.create();
	}

	try {
		return await llm.create( browserParams );
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
 * Wraps the browser text stream to match the candidates API syntax.
 *
 * @since 0.3.0
 *
 * @param resultTextGenerator - The browser text stream.
 * @returns The candidates generator.
 */
async function* wrapBrowserTextGenerator(
	resultTextGenerator: AsyncGenerator< string, void, void >
): AsyncCandidatesGenerator {
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
	 * @param content - Content data to pass to the model, including the prompt and optional history.
	 * @returns Model response candidates with the generated text content.
	 */
	async generateText(
		content: string | Part[] | Content | Content[]
	): Promise< Candidates > {
		validateCapabilities( this.metadata.capabilities, [
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
	 * @param content - Content data to pass to the model, including the prompt and optional history.
	 * @returns The generator that yields chunks of response candidates with the generated text  content.
	 */
	async streamGenerateText(
		content: string | Part[] | Content | Content[]
	): Promise< AsyncCandidatesGenerator > {
		validateCapabilities( this.metadata.capabilities, [
			enums.AiCapability.TEXT_GENERATION,
		] );

		// Do some very basic validation.
		validateContent( content );

		const session = await createSession( this.modelParams );
		const text = prepareContentForBrowser( content );
		const resultTextStream = session.promptStreaming( text );
		const resultTextGenerator = getResponseGenerator( resultTextStream );

		// Normalize result shape to match candidates API syntax from other services.
		return wrapBrowserTextGenerator( resultTextGenerator );
	}
}
