/**
 * Internal dependencies
 */
import GenerativeAiModel from './generative-ai-model';
import * as enums from '../enums';
import { textToContent, base64DataUrlToBase64Data } from '../helpers';
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
interface LanguageModelFactory {
	create: (
		options?: LanguageModelCreateOptions
	) => Promise< LanguageModel >;
	availability: (
		options?: LanguageModelCreateCoreOptions
	) => Promise< Availability >;
}
type Availability =
	| 'unavailable'
	| 'downloadable'
	| 'downloading'
	| 'available';
type LanguageModelMessageType = 'text' | 'image' | 'audio';
interface LanguageModelExpected {
	type: LanguageModelMessageType;
	languages?: string[];
}
interface LanguageModelCreateCoreOptions {
	topK?: number;
	temperature?: number;
	expectedInputs?: LanguageModelExpected[];
	expectedOutputs?: LanguageModelExpected[];
}
interface LanguageModelCreateOptions extends LanguageModelCreateCoreOptions {
	initialPrompts?:
		| [ LanguageModelSystemMessage, ...LanguageModelMessage[] ]
		| LanguageModelMessage[];
}
type LanguageModelMessageValue =
	| ImageBitmapSource
	| AudioBuffer
	| BufferSource
	| string;
interface LanguageModelMessageContent {
	type: LanguageModelMessageType;
	value: LanguageModelMessageValue;
}
interface LanguageModelMessage {
	role: LanguageModelMessageRole;
	content: LanguageModelMessageContent[] | string;
}
interface LanguageModelSystemMessage {
	role: LanguageModelSystemMessageRole;
	content: LanguageModelMessageContent[] | string;
}
type LanguageModelMessageRole = 'user' | 'assistant';
type LanguageModelSystemMessageRole = 'system';
interface LanguageModel {
	prompt: (
		input: LanguageModelPrompt,
		options?: LanguageModelPromptOptions
	) => Promise< string >;
	promptStreaming: (
		input: LanguageModelPrompt,
		options?: LanguageModelPromptOptions
	) => ReadableStream< string >;
}
type LanguageModelPrompt = string | LanguageModelMessage[];
interface LanguageModelPromptOptions {
	responseConstraint?: Record< string, unknown >;
	signal?: AbortSignal;
}

/**
 * Translates base64 encoded data to a BufferSource.
 *
 * @since 0.7.1
 *
 * @param base64 - The base64 encoded string.
 * @returns The BufferSource representation of the data.
 */
function base64ToBufferSource( base64: string ): BufferSource {
	const base64Data = base64DataUrlToBase64Data( base64 );

	const binaryString = atob( base64Data );
	const bytes = new Uint8Array( binaryString.length );
	for ( let i = 0; i < binaryString.length; i++ ) {
		bytes[ i ] = binaryString.charCodeAt( i );
	}
	return bytes.buffer;
}

/**
 * Prepares content parts for the browser AI API.
 *
 * @since 0.7.1
 *
 * @param parts - The content parts.
 * @returns The prepared content parts.
 */
function prepareContentPartsForBrowser(
	parts: Part[]
): LanguageModelMessageContent[] | string {
	if ( parts.length === 1 && 'text' in parts[ 0 ] ) {
		return parts[ 0 ].text;
	}

	return parts.map( ( part ) => {
		if ( 'text' in part ) {
			return {
				type: 'text',
				value: part.text,
			};
		}

		if ( 'inlineData' in part ) {
			if ( part.inlineData.mimeType.startsWith( 'audio/' ) ) {
				return {
					type: 'audio',
					value: base64ToBufferSource( part.inlineData.data ),
				};
			}
			if ( part.inlineData.mimeType.startsWith( 'image/' ) ) {
				return {
					type: 'image',
					value: base64ToBufferSource( part.inlineData.data ),
				};
			}
			throw new Error(
				`Unsupported inline data mime type for browser AI: ${ part.inlineData.mimeType }`
			);
		}

		throw new Error( 'Unsupported part type for browser AI.' );
	} );
}

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
): LanguageModelPrompt {
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
			return ( content as Content[] ).map( ( item ) => {
				return {
					role:
						item.role === enums.ContentRole.USER
							? 'user'
							: 'assistant',
					content: prepareContentPartsForBrowser( item.parts ),
				};
			} );
		}

		let parts: Part[];
		if ( 'role' in content[ 0 ] && 'parts' in content[ 0 ] ) {
			parts = content[ 0 ].parts;
		} else {
			// Assuming it's an array of Part if not an array of Content.
			parts = content as Part[];
		}
		const browserContentParts = prepareContentPartsForBrowser( parts );
		if ( typeof browserContentParts === 'string' ) {
			return browserContentParts;
		}
		return [
			{
				role: 'user',
				content: browserContentParts,
			},
		];
	}

	if ( typeof content === 'object' ) {
		const browserContentParts = prepareContentPartsForBrowser(
			content.parts
		);
		if ( typeof browserContentParts === 'string' ) {
			return browserContentParts;
		}
		return [
			{
				role:
					content.role === enums.ContentRole.USER
						? 'user'
						: 'assistant',
				content: browserContentParts,
			},
		];
	}

	throw new Error( 'Invalid content format.' );
}

/**
 * Gets the modalities present in a prompt.
 *
 * @since 0.7.1
 *
 * @param prompt - The prompt to analyze.
 * @returns The set of modalities present in the prompt.
 */
function getBrowserPromptModalities(
	prompt: LanguageModelPrompt
): Set< LanguageModelMessageType > {
	const modalities = new Set< LanguageModelMessageType >();

	if ( typeof prompt === 'string' ) {
		modalities.add( 'text' );
		return modalities;
	}

	for ( const message of prompt ) {
		if ( typeof message.content === 'string' ) {
			modalities.add( 'text' );
		} else {
			for ( const contentItem of message.content ) {
				modalities.add( contentItem.type );
			}
		}
	}

	return modalities;
}

/**
 * Creates a new browser model instance, based on supported model params.
 *
 * See https://github.com/explainers-by-googlers/prompt-api#examples for supported parameters.
 *
 * @since 0.3.0
 * @since 0.4.0 Checks for newer `ai.languageModel` property.
 * @since 0.6.0 Checks for newer `LanguageModel` property.
 * @since 0.7.0 Renamed from `createSession`.
 * @since 0.7.1 Added `inputModalities` parameter.
 *
 * @param modelParams     - Model parameters.
 * @param inputModalities - Optional set of input modalities to consider.
 * @returns The browser model instance.
 */
async function createBrowserLlm(
	modelParams: ModelParams,
	inputModalities?: Set< LanguageModelMessageType >
): Promise< LanguageModel > {
	const browserParams: LanguageModelCreateOptions = {};
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
		const systemPrompt: LanguageModelSystemMessage = {
			role: 'system',
			content: '',
		};
		if ( typeof modelParams.systemInstruction === 'string' ) {
			systemPrompt.content = modelParams.systemInstruction;
		} else if (
			modelParams.systemInstruction.parts.length &&
			'text' in modelParams.systemInstruction.parts[ 0 ]
		) {
			systemPrompt.content =
				modelParams.systemInstruction.parts[ 0 ].text;
		}
		browserParams.initialPrompts = [ systemPrompt ];
	}
	if ( inputModalities ) {
		const expectedInputs: LanguageModelExpected[] = [];
		inputModalities.forEach( ( modality ) => {
			expectedInputs.push( { type: modality, languages: [ 'en' ] } );
		} );
		browserParams.expectedInputs = expectedInputs;
		browserParams.expectedOutputs = [
			{ type: 'text', languages: [ 'en' ] },
		];
	} else {
		browserParams.expectedOutputs = [
			{ type: 'text', languages: [ 'en' ] },
		];
	}

	let llm: LanguageModelFactory | undefined;
	if ( 'LanguageModel' in window ) {
		llm = window.LanguageModel as LanguageModelFactory;
	} else if (
		'ai' in window &&
		typeof window.ai === 'object' &&
		window.ai !== null &&
		'languageModel' in window.ai
	) {
		llm = window.ai.languageModel as LanguageModelFactory;
	}

	if ( ! llm ) {
		throw new Error( 'Browser AI capabilities not available.' );
	}

	if ( Object.keys( browserParams ).length === 0 ) {
		return llm.create( {
			expectedOutputs: [ { type: 'text', languages: [ 'en' ] } ],
		} );
	}

	try {
		return await llm.create( browserParams );
	} catch ( error ) {
		// eslint-disable-next-line no-console
		console.warn(
			'Failed to create browser session with modelParams, therefore creating default session. Original error:',
			error
		);
		return llm.create( {
			expectedOutputs: [ { type: 'text', languages: [ 'en' ] } ],
		} );
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

		const prompt = prepareContentForBrowser( content );
		const modalities = getBrowserPromptModalities( prompt );
		const llm = await createBrowserLlm( this.modelParams, modalities );
		const resultText = await llm.prompt( prompt );

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

		const prompt = prepareContentForBrowser( content );
		const modalities = getBrowserPromptModalities( prompt );
		const llm = await createBrowserLlm( this.modelParams, modalities );
		const resultTextStream = llm.promptStreaming( prompt );
		const resultTextGenerator = getResponseGenerator( resultTextStream );

		// Normalize result shape to match candidates API syntax from other services.
		return wrapBrowserTextGenerator( resultTextGenerator );
	}
}
