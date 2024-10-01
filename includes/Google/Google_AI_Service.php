<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Google_AI_Service
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google;

use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Types\Parts;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use InvalidArgumentException;

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
	 * @param Authentication $api_key The API key.
	 * @param HTTP           $http    Optional. The HTTP instance to use for requests. Default is a new instance.
	 */
	public function __construct( Authentication $api_key, HTTP $http = null ) {
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
	 * Gets the list of AI capabilities that the service and its models support.
	 *
	 * @since n.e.x.t
	 * @see AI_Capabilities
	 *
	 * @return string[] The list of AI capabilities.
	 */
	public function get_capabilities(): array {
		return AI_Capabilities::get_model_class_capabilities( Google_AI_Model::class );
	}

	/**
	 * Gets the default model slug to use with the service when none is provided.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The default model slug.
	 */
	public function get_default_model_slug(): string {
		return 'gemini-1.5-flash';
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
				esc_html__( 'The response from the Google AI API is missing the "models" key.', 'ai-services' )
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
	 * @param array<string, mixed> $model_params    {
	 *     Optional. Model parameters. Default empty array.
	 *
	 *     @type string                                  $model              The model slug. By default, the service's
	 *                                                                       default model slug is used.
	 *     @type array<string, mixed>                    $generation_config  Model generation configuration options.
	 *                                                                       Default empty array.
	 *     @type string|Parts|Content                    $system_instruction The system instruction for the model.
	 *                                                                       Default none.
	 *     @type Safety_Setting[]|array<string, mixed>[] $safety_settings    Optional. The safety settings for the
	 *                                                                       model. Default empty array.
	 * }
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 * @throws Generative_AI_Exception Thrown if getting the model fails.
	 */
	public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		if ( isset( $model_params['model'] ) ) {
			$model = $model_params['model'];
			unset( $model_params['model'] );
		} else {
			$model = $this->get_default_model_slug();
		}

		return new Google_AI_Model( $this->api, $model, $model_params, $request_options );
	}
}
