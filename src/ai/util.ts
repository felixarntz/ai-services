/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import * as enums from './enums';
import type {
	Content,
	Part,
	ModelParams,
	AiCapability,
	ModelMetadata,
} from './types';

/**
 * Formats the various supported formats of new user content into a consistent content shape.
 *
 * @since 0.1.0
 *
 * @param content - New content.
 * @returns The formatted new content.
 */
export function formatNewContent(
	content: string | Part[] | Content
): Content {
	if ( typeof content === 'string' ) {
		return {
			role: enums.ContentRole.USER,
			parts: [ { text: content } ],
		};
	}

	if ( Array.isArray( content ) ) {
		return {
			role: enums.ContentRole.USER,
			parts: content,
		};
	}

	if (
		typeof content !== 'object' ||
		content === null ||
		! ( 'role' in content ) ||
		! ( 'parts' in content )
	) {
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
 * @param content - Content data to pass to the model, including the prompt and optional history.
 */
export function validateContent(
	content: string | Part[] | Content | Content[]
) {
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
			if ( ! ( 'role' in content ) || ! ( 'parts' in content ) ) {
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
 * @param history - Chat history contents.
 */
export function validateChatHistory( history: Content[] ) {
	history.forEach( ( content, index ) => {
		if ( ! ( 'role' in content ) || ! ( 'parts' in content ) ) {
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
 * @since 0.3.0
 *
 * @param modelParams - Model parameters.
 */
export function validateModelParams( modelParams: ModelParams ) {
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
 * @since 0.3.0
 *
 * @param availableCapabilities - Available capabilities.
 * @param requestedCapabilities - Requested capabilities.
 */
export function validateCapabilities(
	availableCapabilities: AiCapability[],
	requestedCapabilities: AiCapability[]
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
 * Detects the requested capabilities from the content.
 *
 * @since 0.3.0
 *
 * @param content          - Content data to pass to the model, including the prompt and optional history.
 * @param baseCapabilities - Optional. Base capabilities to include in the detected capabilities.
 * @returns Detected capabilities.
 */
export function detectRequestedCapabilitiesFromContent(
	content: string | Part[] | Content | Content[],
	baseCapabilities: AiCapability[] = []
): AiCapability[] {
	const requestedCapabilities = [ ...baseCapabilities ];

	// Multi-turn conversation.
	if (
		Array.isArray( content ) &&
		content.length > 1 &&
		'role' in content[ 0 ] &&
		'parts' in content[ 0 ]
	) {
		requestedCapabilities.push( enums.AiCapability.CHAT_HISTORY );
	}

	return requestedCapabilities;
}

/**
 * Finds a model from the available models based on the given model parameters.
 *
 * An exception is thrown if no matching model is found based on the given model parameters.
 *
 * @since 0.3.0
 *
 * @param availableModels - Metadata for each model, mapped by model slug.
 * @param modelParams     - Model parameters. Should contain either a 'model' slug or requested 'capabilities'.
 * @returns Model data object.
 */
export function findModel(
	availableModels: Record< string, ModelMetadata >,
	modelParams: ModelParams
): ModelMetadata {
	// Find model by slug, if specified.
	if ( modelParams.model ) {
		if ( ! availableModels[ modelParams.model ] ) {
			throw new Error(
				__(
					'The specified model is not available for the service.',
					'ai-services'
				)
			);
		}

		return availableModels[ modelParams.model ];
	}

	/*
	 * Find model based on capabilities.
	 * If no capabilities are specified, assume text generation as reasonable default.
	 */
	const requestedCapabilities = modelParams.capabilities || [
		enums.AiCapability.TEXT_GENERATION,
	];

	for ( const model of Object.keys( availableModels ) ) {
		if (
			requestedCapabilities.every( ( capability ) =>
				availableModels[ model ].capabilities.includes( capability )
			)
		) {
			return availableModels[ model ];
		}
	}

	throw new Error(
		__(
			'No model is available for the specified capabilities.',
			'ai-services'
		)
	);
}
