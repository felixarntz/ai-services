<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Base;

use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use RuntimeException;

/**
 * Base class for an AI model.
 *
 * @since 0.5.0
 */
abstract class Abstract_AI_Model implements Generative_AI_Model {

	/**
	 * The model metadata.
	 *
	 * @since n.e.x.t
	 * @var Model_Metadata
	 */
	private $metadata;

	/**
	 * The request options.
	 *
	 * @since 0.5.0
	 * @var array<string, mixed>
	 */
	private $request_options;

	/**
	 * Gets the model slug.
	 *
	 * @since 0.5.0
	 *
	 * @return string The model slug.
	 */
	final public function get_model_slug(): string {
		return $this->get_model_metadata()->get_slug();
	}

	/**
	 * Gets the model metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @return Model_Metadata The model metadata.
	 *
	 * @throws RuntimeException Thrown if the model metadata is not set.
	 */
	final public function get_model_metadata(): Model_Metadata {
		if ( ! $this->metadata instanceof Model_Metadata ) {
			throw new RuntimeException( 'Model metadata must be set in the constructor.' );
		}

		return $this->metadata;
	}

	/**
	 * Gets the request options.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> The request options.
	 */
	final protected function get_request_options(): array {
		return $this->request_options;
	}

	/**
	 * Sets the model metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @param Model_Metadata $metadata The model metadata.
	 */
	final protected function set_model_metadata( Model_Metadata $metadata ): void {
		$this->metadata = $metadata;
	}

	/**
	 * Sets the request options.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options The request options.
	 */
	final protected function set_request_options( array $request_options ): void {
		$this->request_options = $request_options;
	}
}
