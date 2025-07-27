<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Google_AI_Service
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\HTTP\HTTP_With_Streams;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;

/**
 * Class for the Google AI service.
 *
 * @since 0.1.0
 * @since 0.7.0 Now extends `Abstract_AI_Service`.
 */
class Google_AI_Service extends Abstract_AI_Service implements With_API_Client {
	use With_API_Client_Trait;

	const DEFAULT_API_BASE_URL = 'https://generativelanguage.googleapis.com';
	const DEFAULT_API_VERSION  = 'v1beta';

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Service_Metadata $metadata        The service metadata.
	 * @param Authentication   $authentication  The authentication credentials.
	 * @param Request_Handler  $request_handler Optional. The request handler instance to use for requests. Default is a
	 *                                          new HTTP_With_Streams instance.
	 */
	public function __construct( Service_Metadata $metadata, Authentication $authentication, ?Request_Handler $request_handler = null ) {
		$this->set_service_metadata( $metadata );
		$this->set_api_client(
			new Google_AI_API_Client(
				self::DEFAULT_API_BASE_URL,
				self::DEFAULT_API_VERSION,
				'Google Generative Language',
				$request_handler ?? new HTTP_With_Streams(),
				$authentication
			)
		);
	}

	/**
	 * Lists the available generative model slugs and their metadata.
	 *
	 * @since 0.1.0
	 * @since 0.5.0 Return type changed to a map of model data shapes.
	 * @since 0.7.0 Return type changed to a map of model metadata objects.
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return array<string, Model_Metadata> Metadata for each model, mapped by model slug.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function list_models( array $request_options = array() ): array {
		$api = $this->get_api_client();

		$request       = $api->create_get_request(
			'models',
			array(
				// 1000 is the maximum page size - we just want to retrieve all models in one go.
				'pageSize' => 1000,
			),
			$request_options
		);
		$response_data = $api->make_request( $request )->get_data();

		if ( ! isset( $response_data['models'] ) || ! $response_data['models'] ) {
			throw $api->create_missing_response_key_exception( 'models' );
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

						if ( // Web search is supported by Gemini 2.0 and newer.
							str_starts_with( $model_slug, 'gemini-' ) &&
							! str_starts_with( $model_slug, 'gemini-1.5-' )
						) {
							$model_caps[] = AI_Capability::WEB_SEARCH;
						}

						if ( // New multimodal output model for image generation.
							str_contains( $model_slug, 'image-generation' ) ||
							str_starts_with( $model_slug, 'gemini-2.0-flash-exp' )
						) {
							$model_caps[] = AI_Capability::MULTIMODAL_OUTPUT;
						} elseif ( // New multimodal output model for audio generation.
							str_contains( $model_slug, '-tts' )
						) {
							$model_caps[] = AI_Capability::MULTIMODAL_OUTPUT;
						}
					}
				} elseif (
					isset( $model_data['supportedGenerationMethods'] ) &&
					in_array( 'predict', $model_data['supportedGenerationMethods'], true )
				) {
					$model_caps = $imagen_capabilities;
				} else {
					$model_caps = array();
				}

				$models_data[ $model_slug ] = Model_Metadata::from_array(
					array(
						'slug'         => $model_slug,
						'name'         => $model_data['displayName'] ?? $model_slug,
						'capabilities' => $model_caps,
					)
				);
				return $models_data;
			},
			array()
		);
	}

	/**
	 * Creates a new model instance for the provided model metadata and parameters.
	 *
	 * @since 0.7.0
	 *
	 * @param Model_Metadata       $model_metadata  The model metadata.
	 * @param array<string, mixed> $model_params    Model parameters. See {@see Generative_AI_Service::get_model()} for
	 *                                              a list of available parameters.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Generative_AI_Model The new model instance.
	 */
	protected function create_model_instance( Model_Metadata $model_metadata, array $model_params, array $request_options ): Generative_AI_Model {
		$model_class = AI_Capabilities::get_model_class_for_capabilities(
			array(
				Google_AI_Text_Generation_Model::class,
				Google_AI_Image_Generation_Model::class,
			),
			$model_metadata->get_capabilities()
		);

		return new $model_class(
			$this->get_api_client(),
			$model_metadata,
			$model_params,
			$request_options
		);
	}

	/**
	 * Sorts model slugs by preference.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $model_slugs The model slugs to sort.
	 * @return string[] The model slugs, sorted by preference.
	 */
	protected function sort_models_by_preference( array $model_slugs ): array {
		// Prioritize latest, non-experimental models, preferring cheaper ones.
		$get_preference_group = static function ( $model_slug ) {
			if ( str_starts_with( $model_slug, 'gemini-2.0' ) ) {
				if ( str_ends_with( $model_slug, '-flash' ) ) {
					return 0;
				}
				return 1;
			}
			if ( str_starts_with( $model_slug, 'gemini-' ) ) {
				if ( str_ends_with( $model_slug, '-flash' ) ) {
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
