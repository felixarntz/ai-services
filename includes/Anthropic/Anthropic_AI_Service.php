<?php
/**
 * Class Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_Service
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Anthropic;

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
 * Class for the Anthropic AI service.
 *
 * @since 0.1.0
 */
class Anthropic_AI_Service implements Generative_AI_Service, With_API_Client {

	/**
	 * The Anthropic AI API instance.
	 *
	 * @since 0.1.0
	 * @var Anthropic_AI_API_Client
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Authentication $authentication The authentication credentials.
	 * @param HTTP           $http           Optional. The HTTP instance to use for requests. Default is a new instance.
	 */
	public function __construct( Authentication $authentication, HTTP $http = null ) {
		if ( ! $http ) {
			$http = new HTTP();
		}
		$this->api = new Anthropic_AI_API_Client( $authentication, $http );
	}

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string {
		return 'anthropic';
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
		return AI_Capabilities::get_model_class_capabilities( Anthropic_AI_Model::class );
	}

	/**
	 * Gets the API client instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Generative_AI_API_Client The API client instance.
	 */
	public function get_api_client(): Generative_AI_API_Client {
		return $this->api;
	}

	/**
	 * Gets the default model slug to use with the service when none is provided.
	 *
	 * @since 0.1.0
	 *
	 * @return string The default model slug.
	 */
	public function get_default_model_slug(): string {
		return 'claude-3-sonnet-20240229';
	}

	/**
	 * Lists the available generative model slugs.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string[] The available model slugs.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function list_models( array $request_options = array() ): array {
		return array(
			'claude-3-opus-20240229',
			'claude-3-sonnet-20240229',
			'claude-3-haiku-20240307',
			'claude-3-5-sonnet-20240620',
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
	 *     @type string               $feature            Required. Unique identifier of the feature that the model
	 *                                                    will be used for. Must only contain lowercase letters,
	 *                                                    numbers, hyphens.
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
		if ( isset( $model_params['model'] ) ) {
			$model = $model_params['model'];
			unset( $model_params['model'] );
		} else {
			$model = $this->get_default_model_slug();
		}

		return new Anthropic_AI_Model( $this->api, $model, $model_params, $request_options );
	}
}
