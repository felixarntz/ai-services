/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Service class.
 *
 * @since n.e.x.t
 */
export class GenerativeAiService {
	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Object}   service                  Service object.
	 * @param {string}   service.slug             Service slug.
	 * @param {string}   service.name             Service name.
	 * @param {string[]} service.capabilities     AI capabilities that the service supports.
	 * @param {string[]} service.available_models Available models.
	 */
	constructor( { slug, name, capabilities, available_models: models } ) {
		if ( ! models || ! models.length ) {
			throw new Error(
				`No models available for the service ${ slug }. Is it available?`
			);
		}

		this.slug = slug;
		this.name = name;
		this.capabilities = capabilities;
		this.models = models;
	}

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return {string} Service name.
	 */
	getServiceSlug() {
		return this.slug;
	}

	/**
	 * Gets the list of AI capabilities that the service and its models support.
	 *
	 * @since n.e.x.t
	 *
	 * @return {string[]} The list of AI capabilities.
	 */
	getCapabilities() {
		return this.capabilities;
	}

	/**
	 * Lists the available generative model slugs.
	 *
	 * @since n.e.x.t
	 *
	 * @return {string[]} The available model slugs.
	 */
	listModels() {
		return this.models;
	}

	/**
	 * Generates text content using the service.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Object}                 args             Arguments for generating content.
	 * @param {string|Object|Object[]} args.content     Content data to pass to the model, including the prompt and optional history.
	 * @param {string}                 args.model       Model slug.
	 * @param {Object}                 args.modelParams Model parameters.
	 * @return {Promise<Object[]>} Model response candidates with the generated text content.
	 */
	async generateText( { content, model, modelParams } ) {
		if ( ! this.capabilities.includes( 'text-generation' ) ) {
			throw new Error(
				__(
					'The service does not support text generation.',
					'wp-starter-plugin'
				)
			);
		}

		// Do some very basic validation.
		if ( ! content ) {
			throw new Error(
				__(
					'The content argument is required to generate content.',
					'wp-starter-plugin'
				)
			);
		}
		if ( ! Array.isArray( content ) ) {
			if ( typeof content === 'object' ) {
				if ( ! content.role || ! content.parts ) {
					throw new Error(
						__(
							'The content object must have a role and parts properties.',
							'wp-starter-plugin'
						)
					);
				}
			} else if ( typeof content !== 'string' ) {
				throw new Error(
					__(
						'The content argument must be a string, an object, or an array of objects.',
						'wp-starter-plugin'
					)
				);
			}
		}

		try {
			return await apiFetch( {
				path: `/wp-starter-plugin/v1/services/${ this.slug }:generate-text`,
				method: 'POST',
				data: {
					content,
					model: model || '',
					modelParams: modelParams || {},
				},
			} );
		} catch ( error ) {
			throw new Error( error.message || error.code || error );
		}
	}
}

const services = {};

/**
 * Gets the generative AI service instance for the given service data.
 *
 * The service data must be an object received from the services REST endpoint.
 *
 * @since n.e.x.t
 *
 * @param {Object}   serviceData                  Service data.
 * @param {string}   serviceData.slug             Service slug.
 * @param {string}   serviceData.name             Service name.
 * @param {string[]} serviceData.available_models Available models.
 * @return {GenerativeAiService} Generative AI service instance.
 */
export function getGenerativeAiService( serviceData ) {
	if ( ! services[ serviceData.slug ] ) {
		services[ serviceData.slug ] = new GenerativeAiService( serviceData );
	}

	return services[ serviceData.slug ];
}
