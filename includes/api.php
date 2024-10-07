<?php
/**
 * API functions.
 *
 * @since 0.1.0
 * @package ai-services
 */

use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services\Services\Services_API_Instance;

/**
 * Returns the AI services API instance, which is used to interact with the AI services.
 *
 * Examples:
 *
 * ```
 * ai_services()->get_available_service()
 *   ->get_model( array( 'feature' => 'my-test-feature' ) )
 *   ->generate_text( 'How can you help me?' )
 * ```
 *
 * ```
 * ai_services()->get_available_service( 'google' )
 *   ->get_model(
 *     array(
 *       'feature' => 'my-test-feature',
 *       'model'   => 'gemini-1.5-pro',
 *     )
 *   )
 *   ->generate_text( 'How can you help me?' )
 * ```
 *
 * @since 0.1.0
 *
 * @return Services_API The API instance.
 */
function ai_services() {
	return Services_API_Instance::get();
}
