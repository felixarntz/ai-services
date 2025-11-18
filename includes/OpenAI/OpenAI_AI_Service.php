<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Service
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Service;
use Felix_Arntz\AI_Services\Services\Base\Generic_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\HTTP\HTTP_With_Streams;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;

/**
 * Class for the OpenAI AI service.
 *
 * @since 0.1.0
 * @since 0.7.0 Now extends `Abstract_AI_Service`.
 */
class OpenAI_AI_Service extends Abstract_AI_Service implements With_API_Client {
	use With_API_Client_Trait;

	const DEFAULT_API_BASE_URL = 'https://api.openai.com';
	const DEFAULT_API_VERSION  = 'v1';

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
			new Generic_AI_API_Client(
				self::DEFAULT_API_BASE_URL,
				self::DEFAULT_API_VERSION,
				'OpenAI',
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

		$request       = $api->create_get_request( 'models', array(), $request_options );
		$response_data = $api->make_request( $request )->get_data();

		if ( ! isset( $response_data['data'] ) || ! $response_data['data'] ) {
			throw $api->create_missing_response_key_exception( 'data' );
		}

		// Unfortunately, the OpenAI API does not return model capabilities, so we have to hardcode them here.
		$gpt_capabilities            = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::FUNCTION_CALLING,
			AI_Capability::TEXT_GENERATION,
		);
		$gpt_multimodal_capabilities = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::FUNCTION_CALLING,
			AI_Capability::MULTIMODAL_INPUT,
			AI_Capability::TEXT_GENERATION,
		);
		$image_capabilities          = array(
			AI_Capability::IMAGE_GENERATION,
		);
		$tts_capabilities            = array(
			AI_Capability::TEXT_TO_SPEECH,
		);

		return array_reduce(
			$response_data['data'],
			static function ( array $models_data, array $model_data ) use ( $gpt_capabilities, $gpt_multimodal_capabilities, $image_capabilities, $tts_capabilities ) {
				$model_slug = $model_data['id'];

				if (
					str_starts_with( $model_slug, 'dall-e-' ) ||
					str_starts_with( $model_slug, 'gpt-image-' )
				) {
					$model_caps = $image_capabilities;
				} elseif (
					str_starts_with( $model_slug, 'tts-' ) ||
					str_contains( $model_slug, '-tts' )
				) {
					$model_caps = $tts_capabilities;
				} elseif (
					( str_starts_with( $model_slug, 'gpt-' ) || str_starts_with( $model_slug, 'o1-' ) )
					&& ! str_contains( $model_slug, '-instruct' )
					&& ! str_contains( $model_slug, '-realtime' )
				) {
					if ( str_starts_with( $model_slug, 'gpt-4o' ) ) {
						$model_caps = $gpt_multimodal_capabilities;
						// New multimodal output model for audio generation.
						if ( str_contains( $model_slug, '-audio' ) ) {
							$model_caps[] = AI_Capability::MULTIMODAL_OUTPUT;
						}
					} elseif ( ! str_contains( $model_slug, '-audio' ) ) {
						$model_caps = $gpt_capabilities;
					} else {
						$model_caps = array();
					}
				} else {
					$model_caps = array();
				}

				$models_data[ $model_slug ] = Model_Metadata::from_array(
					// The OpenAI API does not return a display name, so 'name' is omitted to auto-generate.
					array(
						'slug'         => $model_slug,
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
				OpenAI_AI_Text_Generation_Model::class,
				OpenAI_AI_Image_Generation_Model::class,
				OpenAI_AI_Text_To_Speech_Model::class,
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
		usort( $model_slugs, array( $this, 'model_sort_callback' ) );
		return $model_slugs;
	}

	/**
	 * Callback function for sorting models by slug, to be used with `usort()`.
	 *
	 * This method expresses preferences for certain models or model families within the provider by putting them
	 * earlier in the sorted list. The objective is not to be opinionated about which models are better, but to ensure
	 * that more commonly used, more recent, or flagship models are presented first to users.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $a_slug First model slug.
	 * @param string $b_slug Second model slug.
	 * @return int Comparison result.
	 */
	private function model_sort_callback( string $a_slug, string $b_slug ): int {
		// Prefer non-preview models over preview models.
		if ( str_contains( $a_slug, '-preview' ) && ! str_contains( $b_slug, '-preview' ) ) {
			return 1;
		}
		if ( str_contains( $b_slug, '-preview' ) && ! str_contains( $a_slug, '-preview' ) ) {
			return -1;
		}

		// Prefer GPT models over non-GPT models.
		if ( str_starts_with( $a_slug, 'gpt-' ) && ! str_starts_with( $b_slug, 'gpt-' ) ) {
			return -1;
		}
		if ( str_starts_with( $b_slug, 'gpt-' ) && ! str_starts_with( $a_slug, 'gpt-' ) ) {
			return 1;
		}

		// Prefer GPT models with version numbers (e.g. 'gpt-5.1', 'gpt-5') over those without.
		$a_match = preg_match( '/^gpt-([0-9.]+)(-[a-z0-9-]+)?$/', $a_slug, $a_matches );
		$b_match = preg_match( '/^gpt-([0-9.]+)(-[a-z0-9-]+)?$/', $b_slug, $b_matches );
		if ( $a_match && ! $b_match ) {
			return -1;
		}
		if ( $b_match && ! $a_match ) {
			return 1;
		}
		if ( $a_match && $b_match ) {
			// Prefer later model versions.
			$a_version = $a_matches[1];
			$b_version = $b_matches[1];
			if ( version_compare( $a_version, $b_version, '>' ) ) {
				return -1;
			}
			if ( version_compare( $b_version, $a_version, '>' ) ) {
				return 1;
			}

			// Prefer models without a suffix (i.e. base models) over those with a suffix.
			if ( ! isset( $a_matches[2] ) && isset( $b_matches[2] ) ) {
				return -1;
			}
			if ( ! isset( $b_matches[2] ) && isset( $a_matches[2] ) ) {
				return 1;
			}

			// Prefer '-mini' models over others with a suffix.
			if ( isset( $a_matches[2] ) && isset( $b_matches[2] ) ) {
				if ( '-mini' === $a_matches[2] && '-mini' !== $b_matches[2] ) {
					return -1;
				}
				if ( '-mini' === $b_matches[2] && '-mini' !== $a_matches[2] ) {
					return 1;
				}

				// Otherwise, prefer '-turbo' models over others with a suffix.
				if ( '-turbo' === $a_matches[2] && '-turbo' !== $b_matches[2] ) {
					return -1;
				}
				if ( '-turbo' === $b_matches[2] && '-turbo' !== $a_matches[2] ) {
					return 1;
				}
			}
		}

		// Fallback: Sort alphabetically.
		return strcmp( $a_slug, $b_slug );
	}
}
