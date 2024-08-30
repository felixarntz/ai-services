/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

let browser;

/**
 * Gets the data for the client-side exclusive generative AI service 'browser'.
 *
 * @since n.e.x.t
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
			name: __( 'Browser built-in AI', 'wp-starter-plugin' ),
			is_available: capabilities.length > 0,
			capabilities,
			available_models: capabilities.length > 0 ? [ 'default' ] : [],
		};
	}
	return browser;
}

/**
 * Gets the AI capabilities that the browser supports.
 *
 * @since n.e.x.t
 *
 * @param {Object} ai The browser AI API object.
 * @return {Promise<string[]>} The list of AI capabilities.
 */
async function getBrowserAiCapabilities( ai ) {
	const capabilities = [];

	if ( ai.canCreateTextSession ) {
		const supportsTextGeneration = await ai.canCreateTextSession();
		if ( supportsTextGeneration && supportsTextGeneration === 'readily' ) {
			capabilities.push( 'text-generation' );
		}
	}

	return capabilities;
}
