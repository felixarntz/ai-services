<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Decorators\AI_Service_Decorator
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Decorators;

use Felix_Arntz\AI_Services\Services\Cache\Service_Request_Cache;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\Types\Parts;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use InvalidArgumentException;

/**
 * Class for an AI service that wraps another AI service through a decorator pattern.
 *
 * This class effectively acts as middleware for the underlying AI service, allowing for additional functionality to be
 * centrally provided.
 *
 * @since 0.1.0
 */
class AI_Service_Decorator implements Generative_AI_Service {

	/**
	 * The underlying AI service to wrap.
	 *
	 * @since 0.1.0
	 * @var Generative_AI_Service
	 */
	private $service;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Generative_AI_Service $service The underlying AI service to wrap.
	 */
	public function __construct( Generative_AI_Service $service ) {
		$this->service = $service;
	}

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string {
		return $this->service->get_service_slug();
	}

	/**
	 * Gets the list of AI capabilities that the service and its models support.
	 *
	 * @since 0.1.0
	 * @see AI_Capabilities
	 *
	 * @return string[] The list of AI capabilities.
	 */
	public function get_capabilities(): array {
		return $this->service->get_capabilities();
	}

	/**
	 * Checks whether the service is connected.
	 *
	 * This is typically used to check whether the current service credentials are valid.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the service is connected, false otherwise.
	 */
	public function is_connected(): bool {
		return Service_Request_Cache::wrap_transient(
			$this->get_service_slug(),
			array( $this->service, 'is_connected' )
		);
	}

	/**
	 * Lists the available generative model slugs and their capabilities.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return array<string, string[]> Map of the available model slugs and their capabilities.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function list_models( array $request_options = array() ): array {
		return Service_Request_Cache::wrap_transient(
			$this->get_service_slug(),
			array( $this->service, 'list_models' ),
			array( $request_options )
		);
	}

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $model_params    {
	 *     Optional. Model parameters. Default empty array.
	 *
	 *     @type string               $feature           Required. Unique identifier of the feature that the model
	 *                                                   will be used for. Must only contain lowercase letters,
	 *                                                   numbers, hyphens.
	 *     @type string               $model             The model slug. By default, the model will be determined
	 *                                                   based on heuristics such as the requested capabilities.
	 *     @type string[]             $capabilities      Capabilities requested for the model to support. It is
	 *                                                   recommended to specify this if you do not explicitly specify a
	 *                                                   model slug.
	 *     @type Generation_Config?   $generationConfig  Model generation configuration options. Default none.
	 *     @type string|Parts|Content $systemInstruction The system instruction for the model. Default none.
	 * }
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 */
	public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		if ( ! isset( $model_params['feature'] ) || ! preg_match( '/^[a-z0-9-]+$/', $model_params['feature'] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'You must provide a "feature" identifier as part of the model parameters, which only contains lowercase letters, numbers, and hyphens.', 'ai-services' )
			);
		}

		// Perform basic validation so that the model classes don't have to.
		if (
			isset( $model_params['generationConfig'] )
			&& ! $model_params['generationConfig'] instanceof Generation_Config
		) {
			throw new InvalidArgumentException(
				sprintf(
					/* translators: %s: class name */
					esc_html__( 'The generation config argument must be an instance of %s.', 'ai-services' ),
					'Generation_Config'
				)
			);
		}

		if (
			isset( $model_params['systemInstruction'] )
			&& ! is_string( $model_params['systemInstruction'] )
			&& ! $model_params['systemInstruction'] instanceof Parts
			&& ! $model_params['systemInstruction'] instanceof Content
		) {
			throw new InvalidArgumentException(
				sprintf(
					/* translators: 1: class name, 2: another class name */
					esc_html__( 'The system instruction argument must be either a string, or an instance of %1$s, or an instance of %2$s.', 'ai-services' ),
					'Parts',
					'Content'
				)
			);
		}

		return $this->service->get_model( $model_params, $request_options );
	}
}
