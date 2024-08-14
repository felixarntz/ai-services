<?php
/**
 * Interface Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Contracts;

/**
 * Interface for a class representing a generative AI service which provides access to models.
 *
 * @since n.e.x.t
 */
interface Generative_AI_Service {

	/**
	 * Lists the available generative model slugs.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string[] The available model slugs.
	 */
	public function list_models( array $request_options = array() ): array;

	/**
	 * Checks if the generative model with the provided slug exists.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $model The model slug.
	 * @return bool True if the model exists, false otherwise.
	 */
	public function has_model( string $model ): bool;

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params    The model parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 */
	public function get_model( array $model_params, array $request_options = array() ): Generative_AI_Model;
}
