<?php
/**
 * Class Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_Service
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Anthropic;

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
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;

/**
 * Class for the Anthropic AI service.
 *
 * @since 0.1.0
 * @since 0.7.0 Now extends `Abstract_AI_Service`.
 */
class Anthropic_AI_Service extends Abstract_AI_Service implements With_API_Client {
	use With_API_Client_Trait;

	const DEFAULT_API_BASE_URL = 'https://api.anthropic.com';
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
	public function __construct( Service_Metadata $metadata, Authentication $authentication, Request_Handler $request_handler = null ) {
		$this->set_service_metadata( $metadata );
		$this->set_api_client(
			new Anthropic_AI_API_Client(
				self::DEFAULT_API_BASE_URL,
				self::DEFAULT_API_VERSION,
				'Anthropic',
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

		// Unfortunately the Anthropic API does not return model capabilities, so we have to hardcode them here.
		$anthropic_legacy_capabilities = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::TEXT_GENERATION,
		);
		$anthropic_capabilities        = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::FUNCTION_CALLING,
			AI_Capability::MULTIMODAL_INPUT,
			AI_Capability::TEXT_GENERATION,
		);

		return array_reduce(
			$response_data['data'],
			static function ( array $models_data, array $model_data ) use ( $anthropic_legacy_capabilities, $anthropic_capabilities ) {
				$model_slug = $model_data['id'];

				if ( str_starts_with( $model_slug, 'claude-2.' ) ) {
					$model_caps = $anthropic_legacy_capabilities;
				} else {
					$model_caps = $anthropic_capabilities;
					if ( ! preg_match( '/^claude-3-[a-z]+/', $model_slug ) ) { // Only models newer than Claude 3 support web search.
						$model_caps[] = AI_Capability::WEB_SEARCH;
					}
				}

				$models_data[ $model_slug ] = Model_Metadata::from_array(
					array(
						'slug'         => $model_slug,
						'name'         => $model_data['display_name'] ?? $model_slug,
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
		return new Anthropic_AI_Text_Generation_Model(
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
	 * @since 0.7.1
	 *
	 * @param string $a_slug First model slug.
	 * @param string $b_slug Second model slug.
	 * @return int Comparison result.
	 *
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	private function model_sort_callback( string $a_slug, string $b_slug ): int {
		// Prefer Claude models over non-Claude models.
		if ( str_starts_with( $a_slug, 'claude-' ) && ! str_starts_with( $b_slug, 'claude-' ) ) {
			return -1;
		}
		if ( str_starts_with( $b_slug, 'claude-' ) && ! str_starts_with( $a_slug, 'claude-' ) ) {
			return 1;
		}

		/*
		 * Prefer Claude models where the version number isn't the second segment (e.g. 'claude-sonnet-4')
		 * over those where it is (e.g. 'claude-2', 'claude-3-5-sonnet'). The latter is only used for older models.
		 */
		if ( ! preg_match( '/^claude-\d/', $a_slug ) && preg_match( '/^claude-\d/', $b_slug ) ) {
			return -1;
		}
		if ( ! preg_match( '/^claude-\d/', $b_slug ) && preg_match( '/^claude-\d/', $a_slug ) ) {
			return 1;
		}

		/*
		 * Prefer Claude models with type and version number (e.g. 'claude-sonnet-4', 'claude-sonnet-4-5-20250929')
		 * over those without. An optional date suffix may also be present.
		 */
		$a_match = preg_match( '/^claude-([a-z]+)-(\d(-\d)?)(-[0-9]+)?$/', $a_slug, $a_matches );
		$b_match = preg_match( '/^claude-([a-z]+)-(\d(-\d)?)(-[0-9]+)?$/', $b_slug, $b_matches );
		if ( $a_match && ! $b_match ) {
			return -1;
		}
		if ( $b_match && ! $a_match ) {
			return 1;
		}
		if ( $a_match && $b_match ) {
			// Prefer later model versions.
			$a_version = str_replace( '-', '.', $a_matches[2] );
			$b_version = str_replace( '-', '.', $b_matches[2] );
			if ( version_compare( $a_version, $b_version, '>' ) ) {
				return -1;
			}
			if ( version_compare( $b_version, $a_version, '>' ) ) {
				return 1;
			}

			// Prefer models without a suffix (i.e. base models) over those with a suffix.
			if ( ! isset( $a_matches[4] ) && isset( $b_matches[4] ) ) {
				return -1;
			}
			if ( ! isset( $b_matches[4] ) && isset( $a_matches[4] ) ) {
				return 1;
			}

			// Prefer 'sonnet' models over other types.
			if ( 'sonnet' === $a_matches[1] && 'sonnet' !== $b_matches[1] ) {
				return -1;
			}
			if ( 'sonnet' === $b_matches[1] && 'sonnet' !== $a_matches[1] ) {
				return 1;
			}

			// Prefer later release dates.
			if ( isset( $a_matches[4] ) && isset( $b_matches[4] ) ) {
				$a_date = (int) substr( $a_matches[4], 1 );
				$b_date = (int) substr( $b_matches[4], 1 );
				if ( $a_date > $b_date ) {
					return -1;
				}
				if ( $b_date > $a_date ) {
					return 1;
				}
			}
		}

		// Fallback: Sort alphabetically.
		return strcmp( $a_slug, $b_slug );
	}
}
