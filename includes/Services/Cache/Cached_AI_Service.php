<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Cache\Cached_AI_Service
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Cache;

use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;

/**
 * Class representing a cached AI service through a decorator pattern.
 *
 * @since n.e.x.t
 */
class Cached_AI_Service implements Generative_AI_Service {

	/**
	 * The underlying AI service to cache.
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
	 * @param Generative_AI_Service $service The underlying AI service to cache.
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
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 */
	public function get_model( string $model, array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		return $this->service->get_model( $model, $model_params, $request_options );
	}
}
