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
		// Unfortunately the Anthropic API does not return models, so we have to hardcode them here.
		$anthropic_capabilities = array( AI_Capabilities::CAPABILITY_MULTIMODAL_INPUT, AI_Capabilities::CAPABILITY_TEXT_GENERATION );

		return array_fill_keys(
			array(
				'claude-3-opus-20240229',
				'claude-3-sonnet-20240229',
				'claude-3-haiku-20240307',
				'claude-3-5-sonnet-20240620',
			),
			$anthropic_capabilities
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
	 * @throws Generative_AI_Exception Thrown if getting the model fails.
	 */
	public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		if ( isset( $model_params['model'] ) ) {
			$model = $model_params['model'];
			unset( $model_params['model'] );
		} else {
			if ( isset( $model_params['capabilities'] ) ) {
				$model_slugs = AI_Capabilities::get_model_slugs_for_capabilities(
					$this->list_models( $request_options ),
					$model_params['capabilities']
				);
			} else {
				$model_slugs = array_keys( $this->list_models( $request_options ) );
			}
			$model = $this->sort_models_by_preference( $model_slugs )[0];
		}

		return new Anthropic_AI_Model( $this->api, $model, $model_params, $request_options );
	}

	/**
	 * Sorts model slugs by preference.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $model_slugs The model slugs to sort.
	 * @return string[] The model slugs, sorted by preference.
	 */
	private function sort_models_by_preference( array $model_slugs ): array {
		$get_preference_group = static function ( $model_slug ) {
			if ( str_starts_with( $model_slug, 'claude-3' ) ) {
				if ( str_contains( $model_slug, '-sonnet' ) ) {
					return 0;
				}
				return 1;
			}
			if ( str_starts_with( $model_slug, 'claude-' ) ) {
				if ( str_contains( $model_slug, '-sonnet' ) ) {
					return 2;
				}
				return 3;
			}
			return 4;
		};

		$preference_groups = array_fill( 0, 5, array() );
		foreach ( $model_slugs as $model_slug ) {
			$group                         = $get_preference_group( $model_slug );
			$preference_groups[ $group ][] = $model_slug;
		}

		return array_merge( ...$preference_groups );
	}
}
