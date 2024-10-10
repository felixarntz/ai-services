/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

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
				capabilities.length > 0 ? { default: capabilities } : {},
		};
	}
	return browser;
}

/**
 * Gets the AI capabilities that the browser supports.
 *
 * @since 0.1.0
 *
 * @param {Object} ai The browser AI API object.
 * @return {Promise<string[]>} The list of AI capabilities.
 */
async function getBrowserAiCapabilities( ai ) {
	const capabilities = [];

	if ( ai.assistant ) {
		const supportsTextGeneration = await ai.assistant.capabilities();
		if (
			supportsTextGeneration &&
			supportsTextGeneration.available === 'readily'
		) {
			capabilities.push( 'text_generation' );
		}
	}

	return capabilities;
}
