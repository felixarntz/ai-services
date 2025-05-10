<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;

/**
 * Interface for a class representing a generative AI model.
 *
 * @since 0.1.0
 */
interface Generative_AI_Model {

	/**
	 * Gets the model slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The model slug.
	 */
	public function get_model_slug(): string;

	/**
	 * Gets the model metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @return Model_Metadata The model metadata.
	 */
	public function get_model_metadata(): Model_Metadata;
}
