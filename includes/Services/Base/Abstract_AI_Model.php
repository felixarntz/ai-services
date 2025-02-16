<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Base;

use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use InvalidArgumentException;

/**
 * Base class for an AI model.
 *
 * @since n.e.x.t
 */
abstract class Abstract_AI_Model implements Generative_AI_Model {

	/**
	 * The model slug.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $model;

	/**
	 * The request options.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $request_options;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see Generative_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( string $model, array $model_params = array(), array $request_options = array() ) {
		$this->request_options = $request_options;
		$this->model           = $model;
		$this->set_model_params( $model_params );
	}

	/**
	 * Gets the model slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The model slug.
	 */
	final public function get_model_slug(): string {
		return $this->model;
	}

	/**
	 * Gets the request options.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The request options.
	 */
	final protected function get_request_options(): array {
		return $this->request_options;
	}

	/**
	 * Sets the model parameters on the class instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	abstract protected function set_model_params( array $model_params ): void;

	/**
	 * Sets class properties from the given arguments based on the given properties definitions array.
	 *
	 * @since n.e.x.t
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
}
