<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Google\Google_AI_Service
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Google;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_API_Client;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\With_API_Client;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;

/**
 * Class for the Google AI service.
 *
 * @since n.e.x.t
 */
class Google_AI_Service implements Generative_AI_Service, With_API_Client {

	/**
	 * The Google AI API instance.
	 *
	 * @since n.e.x.t
	 * @var Google_AI_API_Client
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $api_key The API key.
	 * @param HTTP   $http    Optional. The HTTP instance to use for requests. Default is a new instance.
	 */
	public function __construct( string $api_key, HTTP $http = null ) {
		if ( ! $http ) {
			$http = new HTTP();
		}
		$this->api = new Google_AI_API_Client( $api_key, $http );
	}

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string {
		return 'google';
	}

	/**
	 * Gets the API client instance.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generative_AI_API_Client The API client instance.
	 */
	public function get_api_client(): Generative_AI_API_Client {
		return $this->api;
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
		$request  = $this->api->create_list_models_request();
		$response = $this->api->make_request( $request );

		if ( ! isset( $response['models'] ) || ! $response['models'] ) {
			throw new Generative_AI_Exception(
				esc_html__( 'The response from the Google AI API is missing the "models" key.', 'wp-starter-plugin' )
			);
		}

		return array_map(
			static function ( array $model ) {
				if ( isset( $model['baseModelId'] ) ) {
					return $model['baseModelId'];
				}
				if ( str_starts_with( $model['name'], 'models/' ) ) {
					return substr( $model['name'], 7 );
				}
				return $model['name'];
			},
			$response['models']
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
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 * @throws Generative_AI_Exception Thrown if getting the model fails.
	 */
	public function get_model( string $model, array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		return new Google_AI_Model( $this->api, $model, $model_params, $request_options );
	}
}
