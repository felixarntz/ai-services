/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import * as enums from '../enums';
import { ServiceResource, AiCapability } from '../types';

/*
 * Using `@types/dom-chromium-ai` does not properly work, so we'll redefine the relevant types here.
 * See https://github.com/DefinitelyTyped/DefinitelyTyped/blob/master/types/dom-chromium-ai/index.d.ts
 */
interface AILanguageModelFactory {
	// Since we only use this method here, no need to define the full type.
	capabilities: () => Promise< AILanguageModelCapabilities >;
}
interface AILanguageModelCapabilities {
	readonly available: AICapabilityAvailability;
}
type AICapabilityAvailability = 'readily' | 'after-download' | 'no';

let browser: ServiceResource;

/**
 * Gets the data for the client-side exclusive generative AI service 'browser'.
 *
 * @since 0.1.0
 *
 * @returns The browser AI service data.
 */
export async function getBrowserServiceData(): Promise< ServiceResource > {
	if ( ! browser ) {
		const capabilities = await getBrowserAiCapabilities();
		browser = {
			slug: 'browser',
			metadata: {
				slug: 'browser',
				name: __( 'Browser built-in AI', 'ai-services' ),
				credentials_url: '',
				type: enums.ServiceType.CLIENT,
				capabilities,
			},
			is_available: capabilities.length > 0,
			available_models:
				capabilities.length > 0
					? {
							default: {
								slug: 'default',
								name: 'Gemini Nano', // The model used in Chrome under the hood.
								capabilities,
							},
					  }
					: {},
			has_forced_api_key: false,
		};
	}
	return browser;
}

/**
 * Gets the AI capabilities that the browser supports.
 *
 * @since 0.1.0
 * @since 0.4.0 Checks for newer `ai.languageModel` property.
 * @since 0.6.0 Checks for newer `LanguageModel` property.
 *
 * @returns The list of AI capabilities.
 */
async function getBrowserAiCapabilities(): Promise< AiCapability[] > {
	const capabilities: AiCapability[] = [];

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

	if ( llm ) {
		const browserAiCapabilities = await llm.capabilities();
		if ( browserAiCapabilities.available === 'readily' ) {
			capabilities.push( enums.AiCapability.TEXT_GENERATION );
		}
	}

	return capabilities;
}
