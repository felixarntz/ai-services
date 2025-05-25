/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import * as enums from '../enums';

let browser;

/**
 * Gets the data for the client-side exclusive generative AI service 'browser'.
 *
 * @since 0.1.0
 *
 * @return {Promise<Object>} The browser AI service data.
 */
export async function getBrowserServiceData() {
	if ( ! browser ) {
		const capabilities = await getBrowserAiCapabilities();
		browser = {
			slug: 'browser',
			name: __( 'Browser built-in AI', 'ai-services' ),
			is_available: capabilities.length > 0,
			type: 'client', // TODO: Introduce a proper type for this differentiation.
			capabilities,
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
 * @return {Promise<string[]>} The list of AI capabilities.
 */
async function getBrowserAiCapabilities() {
	const capabilities = [];

	const llm =
		window.LanguageModel ||
		window.ai?.languageModel ||
		window.ai?.assistant;

	if ( llm && typeof llm.availability === 'function' ) {
		const availability = await llm.availability();
		if ( availability === 'available' ) {
			capabilities.push( enums.AiCapability.TEXT_GENERATION );
		}
	}

	return capabilities;
}
