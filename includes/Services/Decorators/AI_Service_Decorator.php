<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Decorators\AI_Service_Decorator
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Decorators;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\Cache\Service_Request_Cache;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
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
	 * @since 0.2.0
	 *
	 * @return bool True if the service is connected, false otherwise.
	 */
	public function is_connected(): bool {
		if ( ! function_exists( 'get_transient' ) ) {
			// If the transient function is not available, we cannot cache the result.
			return $this->service->is_connected();
		}

		return Service_Request_Cache::wrap_transient(
			$this->get_service_slug(),
			array( $this->service, 'is_connected' )
		);
	}

	/**
	 * Lists the available generative model slugs and their capabilities.
	 *
	 * @since 0.1.0
	 * @since 0.5.0 Return type changed to a map of model data shapes.
	 *
	 * @phpstan-return array<string, array{slug: string, name: string, capabilities: string[]}>
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return array<string, mixed>                 Data for each model, mapped by model slug.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function list_models( array $request_options = array() ): array {
		if ( ! function_exists( 'get_transient' ) ) {
			// If the transient function is not available, we cannot cache the result.
			return $this->service->list_models( $request_options );
		}

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
	 * @since 0.5.0 Support for the $tools and $toolConfig arguments was added.
	 *
	 * @param array<string, mixed> $model_params    {
	 *     Optional. Model parameters. Default empty array.
	 *
	 *     @type string                 $feature           Required. Unique identifier of the feature that the model
	 *                                                     will be used for. Must only contain lowercase letters,
	 *                                                     numbers, hyphens.
	 *     @type string                 $model             The model slug. By default, the model will be determined
	 *                                                     based on heuristics such as the requested capabilities.
	 *     @type string[]               $capabilities      Capabilities requested for the model to support. It is
	 *                                                     recommended to specify this if you do not explicitly specify
	 *                                                     a model slug.
	 *     @type Tools|null             $tools             The tools to use for the model. Default none.
	 *     @type Tool_Config|null       $toolConfig        Tool configuration options. Default none.
	 *     @type Generation_Config|null $generationConfig  Model generation configuration options. Default none.
	 *     @type string|Parts|Content   $systemInstruction The system instruction for the model. Default none.
	 * }
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 */
	public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		if ( ! isset( $model_params['feature'] ) || ! preg_match( '/^[a-z0-9-]+$/', $model_params['feature'] ) ) {
			throw new InvalidArgumentException(
				'You must provide a "feature" identifier as part of the model parameters, which only contains lowercase letters, numbers, and hyphens.'
			);
		}

		/**
		 * Filters the AI service model parameters before retrieving the model with them.
		 *
		 * This can be used, for example, to inject additional parameters via server-side logic based on the given
		 * feature identifier.
		 *
		 * @since 0.2.0
		 *
		 * @param array<string, mixed> $model_params The model parameters. Commonly supports at least the parameters
		 *                                           'feature', 'capabilities', 'generationConfig' and
		 *                                           'systemInstruction'.
		 * @param string               $service_slug The service slug.
		 *
		 * @return array<string, mixed> The processed model parameters.
		 */
		$filtered_model_params = (array) apply_filters( 'ai_services_model_params', $model_params, $this->service->get_service_slug() );

		// Ensure that the feature identifier cannot be changed.
		$filtered_model_params['feature'] = $model_params['feature'];
		$model_params                     = $filtered_model_params;

		// Perform basic validation so that the model classes don't have to.
		$this->validate_model_params( $model_params );

		return $this->service->get_model( $model_params, $request_options );
	}

	/**
	 * Validates various model parameters centrally.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	private function validate_model_params( array $model_params ): void {
		if (
			isset( $model_params['tools'] )
			&& ! $model_params['tools'] instanceof Tools
		) {
			throw new InvalidArgumentException(
				sprintf(
					'The tools argument must be an instance of %s.',
					Tools::class
				)
			);
		}
		if (
			isset( $model_params['toolConfig'] )
			&& ! $model_params['toolConfig'] instanceof Tool_Config
		) {
			throw new InvalidArgumentException(
				sprintf(
					'The tool config argument must be an instance of %s.',
					Tool_Config::class
				)
			);
		}
		if (
			isset( $model_params['generationConfig'] )
			&& ! $model_params['generationConfig'] instanceof Generation_Config
		) {
			throw new InvalidArgumentException(
				sprintf(
					'The generation config argument must be an instance of %s.',
					Generation_Config::class
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
					'The system instruction argument must be either a string, or an instance of %1$s, or an instance of %2$s.',
					'Parts',
					'Content'
				)
			);
		}
	}
}
