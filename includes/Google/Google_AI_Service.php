<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Google_AI_Service
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google;

use Felix_Arntz\AI_Services\Google\Types\Safety_Setting;
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use InvalidArgumentException;

/**
 * Class for the Google AI service.
 *
 * @since 0.1.0
 */
class Google_AI_Service implements Generative_AI_Service {

	/**
	 * The Google AI API instance.
	 *
	 * @since 0.1.0
	 * @var Google_AI_API_Client
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
		$this->api = new Google_AI_API_Client( $authentication, $http );
	}

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string {
		return 'google';
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
		return array_unique(
			array_merge(
				AI_Capabilities::get_model_class_capabilities( Google_AI_Text_Generation_Model::class ),
				AI_Capabilities::get_model_class_capabilities( Google_AI_Image_Generation_Model::class )
			)
		);
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
		try {
			$this->list_models();
			return true;
		} catch ( Generative_AI_Exception $e ) {
			return false;
		}
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
		$request       = $this->api->create_list_models_request( array(), $request_options );
		$response_data = $this->api->make_request( $request )->get_data();

		if ( ! isset( $response_data['models'] ) || ! $response_data['models'] ) {
			throw $this->api->create_missing_response_key_exception( 'models' );
		}

		$gemini_legacy_capabilities = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::FUNCTION_CALLING,
			AI_Capability::TEXT_GENERATION,
		);
		$gemini_capabilities        = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::FUNCTION_CALLING,
			AI_Capability::MULTIMODAL_INPUT,
			AI_Capability::TEXT_GENERATION,
		);
		$imagen_capabilities        = array(
			AI_Capability::IMAGE_GENERATION,
		);

		return array_reduce(
			$response_data['models'],
			static function ( array $models_data, array $model_data ) use ( $gemini_legacy_capabilities, $gemini_capabilities, $imagen_capabilities ) {
				$model_slug = $model_data['baseModelId'] ?? $model_data['name'];
				if ( str_starts_with( $model_slug, 'models/' ) ) {
					$model_slug = substr( $model_slug, 7 );
				}

				if (
					isset( $model_data['supportedGenerationMethods'] ) &&
					in_array( 'generateContent', $model_data['supportedGenerationMethods'], true )
				) {
					if (
						str_starts_with( $model_slug, 'gemini-1.0' ) ||
						str_starts_with( $model_slug, 'gemini-pro' ) // 'gemini-pro' without version refers to 1.0.
					) {
						$model_caps = $gemini_legacy_capabilities;
					} else {
						$model_caps = $gemini_capabilities;
					}
				} elseif (
					isset( $model_data['supportedGenerationMethods'] ) &&
					in_array( 'predict', $model_data['supportedGenerationMethods'], true )
				) {
					$model_caps = $imagen_capabilities;
				} else {
					$model_caps = array();
				}

				$models_data[ $model_slug ] = array(
					'slug'         => $model_slug,
					'name'         => $model_data['displayName'] ?? $model_slug,
					'capabilities' => $model_caps,
				);
				return $models_data;
			},
			array()
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
	 *     @type Safety_Setting[]       $safetySettings    Optional. The safety settings for the model. Default empty
	 *                                                     array.
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

		// TODO: Not ideal to have this hard-coded. Refactor.
		if (
			str_starts_with( $model, 'imagen-' ) ||
			( isset( $model_params['capabilities'] ) && in_array( AI_Capability::IMAGE_GENERATION, $model_params['capabilities'], true ) )
		) {
			return new Google_AI_Image_Generation_Model( $this->api, $model, $model_params, $request_options );
		}

		return new Google_AI_Text_Generation_Model( $this->api, $model, $model_params, $request_options );
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
			if ( str_starts_with( $model_slug, 'gemini-2.0' ) ) {
				if ( str_contains( $model_slug, '-flash' ) ) {
					return 0;
				}
				return 1;
			}
			if ( str_starts_with( $model_slug, 'gemini-' ) ) {
				if ( str_contains( $model_slug, '-flash' ) ) {
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
