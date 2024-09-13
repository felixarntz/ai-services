<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

/**
 * Interface for a class representing a generative AI model.
 *
 * @since n.e.x.t
 */
interface Generative_AI_Model {

	/**
	 * Gets the model slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The model slug.
	 */
	public function get_model_slug(): string;
}
