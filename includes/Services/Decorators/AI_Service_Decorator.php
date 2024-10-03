<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Decorators\AI_Service_Decorator
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Decorators;

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
 * @since n.e.x.t
 */
class AI_Service_Decorator implements Generative_AI_Service {

	/**
	 * The underlying AI service to wrap.
	 *
	 * @since n.e.x.t
	 * @var Generative_AI_Service
	 */
	private $service;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_Service $service The underlying AI service to wrap.
	 */
	public function __construct( Generative_AI_Service $service ) {
		$this->service = $service;
	}

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string {
		return $this->service->get_service_slug();
	}

	/**
	 * Gets the list of AI capabilities that the service and its models support.
	 *
	 * @since n.e.x.t
	 * @see AI_Capabilities
	 *
	 * @return string[] The list of AI capabilities.
	 */
	public function get_capabilities(): array {
		return $this->service->get_capabilities();
	}

	/**
	 * Gets the default model slug to use with the service when none is provided.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The default model slug.
	 */
	public function get_default_model_slug(): string {
		return $this->service->get_default_model_slug();
	}

	/**
	 * Lists the available generative model slugs.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string[] The available model slugs.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function list_models( array $request_options = array() ): array {
		return Service_Request_Cache::wrap_transient(
			$this->get_service_slug(),
			array( $this->service, 'list_models' )
		);
	}

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params    {
	 *     Optional. Model parameters. Default empty array.
	 *
	 *     @type string               $model              The model slug. By default, the service's default model slug
	 *                                                    is used.
	 *     @type array<string, mixed> $generation_config  Model generation configuration options. Default empty array.
	 *     @type string|Parts|Content $system_instruction The system instruction for the model. Default none.
	 * }
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 * @throws Generative_AI_Exception Thrown if getting the model fails.
	 */
	public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		return $this->service->get_model( $model_params, $request_options );
	}
}
