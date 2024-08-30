<?php
/**
 * Interface Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Contracts;

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
