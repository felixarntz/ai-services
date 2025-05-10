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
use InvalidArgumentException;
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
	 * Constructor.
	 *
	 * @since 0.5.0
	 * @since n.e.x.t Now requires model metadata to be passed as first parameter instead of model slug.
	 *
	 * @param Model_Metadata       $metadata        The model metadata.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see Generative_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Model_Metadata $metadata, array $model_params = array(), array $request_options = array() ) {
		$this->request_options = $request_options;
		$this->set_model_metadata( $metadata );
		$this->set_model_params( $model_params );
	}

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
	 * Sets the model parameters on the class instance.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	abstract protected function set_model_params( array $model_params ): void;

	/**
	 * Sets class properties from the given arguments based on the given properties definitions array.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed>[] $prop_definitions The property definitions. Each entry must contain at least the
	 *                                                 'arg_key' and 'proeprty_name' keys, and optionally the
	 *                                                 'sanitize_callback' key.
	 * @param array<string, mixed>   $args             The arguments.
	 */
	final protected function set_props_from_args( array $prop_definitions, array $args ): void {
		foreach ( $prop_definitions as $prop_definition ) {
			$arg_key       = $prop_definition['arg_key'];
			$property_name = $prop_definition['property_name'];
			if ( isset( $args[ $arg_key ] ) ) {
				if ( isset( $prop_definition['sanitize_callback'] ) ) {
					$this->$property_name = $prop_definition['sanitize_callback']( $args[ $arg_key ] );
				} else {
					$this->$property_name = $args[ $arg_key ];
				}
			}
		}
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
}
