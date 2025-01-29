/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import * as enums from './enums';

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
		const capabilities = window.ai
			? await getBrowserAiCapabilities( window.ai )
			: [];
		browser = {
			slug: 'browser',
			name: __( 'Browser built-in AI', 'ai-services' ),
			is_available: capabilities.length > 0,
			capabilities,
			available_models:
				capabilities.length > 0
					? {
							default: {
								slug: 'default',
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
 *
 * @param {Object} ai The browser AI API object.
 * @return {Promise<string[]>} The list of AI capabilities.
 */
async function getBrowserAiCapabilities( ai ) {
	const capabilities = [];

	const llm = ai.languageModel || ai.assistant;

	if ( llm ) {
		const supportsTextGeneration = await llm.capabilities();
		if (
			supportsTextGeneration &&
			supportsTextGeneration.available === 'readily'
		) {
			capabilities.push( enums.AiCapability.TEXT_GENERATION );
		}
	}

	return capabilities;
}
