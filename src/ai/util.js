/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import * as enums from './enums';

/**
 * Formats the various supported formats of new user content into a consistent content shape.
 *
 * @since 0.1.0
 *
 * @param {string|Object|Object[]} content New content.
 * @return {Object} The formatted new content.
 */
export function formatNewContent( content ) {
	if ( typeof content === 'string' ) {
		return {
			role: enums.ContentRole.USER,
			parts: [ { text: content } ],
		};
	}

	if ( Array.isArray( content ) ) {
		// Could be an array of contents or parts.
		if ( content[ 0 ].role || content[ 0 ].parts ) {
			return content;
		}

		return {
			role: enums.ContentRole.USER,
			parts: content,
		};
	}

	if ( ! content.role || ! content.parts ) {
		throw new Error(
			__(
				'The value must be a string, a parts object, or a content object.',
				'ai-services'
			)
		);
	}

	return content;
}

/**
 * Performs some very basic client-side validation for the content argument.
 *
 * @since 0.1.0
 *
 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional history.
 */
export function validateContent( content ) {
	if ( ! content ) {
		throw new Error(
			__(
				'The content argument is required to generate content.',
				'ai-services'
			)
		);
	}
	if ( ! Array.isArray( content ) ) {
		if ( typeof content === 'object' ) {
			if ( ! content.role || ! content.parts ) {
				throw new Error(
					__(
						'The content object must have a role and parts properties.',
						'ai-services'
					)
				);
			}
		} else if ( typeof content !== 'string' ) {
			throw new Error(
				__(
					'The content argument must be a string, an object, or an array of objects.',
					'ai-services'
				)
			);
		}
	}
}

/**
 * Validates the chat history.
 *
 * @since 0.1.0
 *
 * @param {Object[]} history Chat history.
 */
export function validateChatHistory( history ) {
	history.forEach( ( content, index ) => {
		if ( ! content.role || ! content.parts ) {
			throw new Error(
				__(
					'The content object must have a role and parts properties.',
					'ai-services'
				)
			);
		}

		if ( index === 0 && content.role !== enums.ContentRole.USER ) {
			throw new Error(
				__(
					'The first content object in the history must be user content.',
					'ai-services'
				)
			);
		}

		if ( content.parts.length === 0 ) {
			throw new Error(
				__(
					'Each Content instance must have at least one part.',
					'ai-services'
				)
			);
		}
	} );
}

/**
 * Validates the model parameters.
 *
 * @since n.e.x.t
 *
 * @param {Object} modelParams Model parameters.
 */
export function validateModelParams( modelParams ) {
	if ( ! modelParams.feature ) {
		throw new Error(
			__(
				'You must provide a "feature" identifier as part of the model parameters, which only contains lowercase letters, numbers, and hyphens.',
				'ai-services'
			)
		);
	}
}

/**
 * Validates the given available capabilities include all requested capabilities.
 *
 * @since n.e.x.t
 *
 * @param {string[]} availableCapabilities Available capabilities.
 * @param {string[]} requestedCapabilities Requested capabilities.
 */
export function validateCapabilities(
	availableCapabilities,
	requestedCapabilities
) {
	if (
		! requestedCapabilities.every( ( capability ) =>
			availableCapabilities.includes( capability )
		)
	) {
		throw new Error(
			__(
				'The model does not support the requested capabilities.',
				'ai-services'
			)
		);
	}
}

/**
 * Finds a model from the available models based on the given model parameters.
 *
 * @since n.e.x.t
 *
 * @param {Object} availableModels Map of the available model slugs and their capabilities.
 * @param {Object} modelParams     Model parameters. Should contain either a 'model' slug or requested 'capabilities'.
 * @return {Object} Model object containing 'slug' and 'capabilities' properties.
 */
export function findModel( availableModels, modelParams ) {
	// Find model by slug, if specified.
	if ( modelParams.model ) {
		const capabilities = availableModels[ modelParams.model ];

		if ( ! capabilities ) {
			throw new Error(
				__(
					'The specified model is not available for the service.',
					'ai-services'
				)
			);
		}

		return {
			slug: modelParams.model,
			capabilities,
		};
	}

	/*
	 * Find model based on capabilities.
	 * If no capabilities are specified, assume text generation as reasonable default.
	 */
	const requestedCapabilities = modelParams.capabilities || [
		enums.AiCapability.TEXT_GENERATION,
	];

	for ( const model of Object.keys( availableModels ) ) {
		const capabilities = availableModels[ model ];
		if (
			requestedCapabilities.every( ( capability ) =>
				capabilities.includes( capability )
			)
		) {
			return {
				slug: model,
				capabilities,
			};
		}
	}

	throw new Error(
		__(
			'No model is available for the specified capabilities.',
			'ai-services'
		)
	);
}
