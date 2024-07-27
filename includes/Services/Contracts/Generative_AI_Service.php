<?php
/**
 * Interface Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Contracts\Generative_AI_Service
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Contracts;

/**
 * Interface for a class representing a generative AI service which provides access to models.
 *
 * @since n.e.x.t
 */
interface Generative_AI_Service {

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params    The model parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 */
	public function get_generative_model( array $model_params, array $request_options = array() ): Generative_AI_Model;
}
