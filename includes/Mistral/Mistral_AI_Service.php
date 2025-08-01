<?php
/**
 * Class Felix_Arntz\AI_Services\Mistral\Mistral_AI_Service
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Mistral;

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
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;

/**
 * Class for the Mistral AI service.
 *
 * @since 0.7.0
 */
class Mistral_AI_Service extends Abstract_AI_Service implements With_API_Client {
	use With_API_Client_Trait;

	const DEFAULT_API_BASE_URL = 'https://api.mistral.ai';
	const DEFAULT_API_VERSION  = 'v1';

	/**
	 * Constructor.
	 *
	 * @since 0.7.0
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
				'Mistral',
				$request_handler ?? new HTTP_With_Streams(),
				$authentication
			)
		);
	}

	/**
	 * Lists the available generative model slugs and their metadata.
	 *
	 * @since 0.7.0
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

		return array_reduce(
			$response_data['data'],
			static function ( array $models_data, array $model_data ) {
				$model_slug = $model_data['id'];

				$model_caps = array();
				if ( isset( $model_data['capabilities']['completion_chat'] ) && $model_data['capabilities']['completion_chat'] ) {
					$model_caps[] = AI_Capability::TEXT_GENERATION;
					$model_caps[] = AI_Capability::CHAT_HISTORY;
					if ( isset( $model_data['capabilities']['function_calling'] ) && $model_data['capabilities']['function_calling'] ) {
						$model_caps[] = AI_Capability::FUNCTION_CALLING;
					}
					if ( isset( $model_data['capabilities']['vision'] ) && $model_data['capabilities']['vision'] ) {
						$model_caps[] = AI_Capability::MULTIMODAL_INPUT;
					}
				}

				$models_data[ $model_slug ] = Model_Metadata::from_array(
					array_filter(
						array(

							/*
							 * While the model data includes a 'name' key, it is actually just the model slug again.
							 * Therefore we ignore it here.
							 */
							'slug'         => $model_slug,
							'capabilities' => $model_caps,
						)
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
		return new Mistral_AI_Text_Generation_Model(
			$this->get_api_client(),
			$model_metadata,
			$model_params,
			$request_options
		);
	}

	/**
	 * Sorts model slugs by preference.
	 *
	 * @since 0.7.0
	 *
	 * @param string[] $model_slugs The model slugs to sort.
	 * @return string[] The model slugs, sorted by preference.
	 */
	protected function sort_models_by_preference( array $model_slugs ): array {
		// Prioritize latest, non-experimental models, preferring cheaper ones.
		$get_preference_group = static function ( $model_slug ) {
			if ( str_ends_with( $model_slug, '-latest' ) ) {
				if ( str_starts_with( $model_slug, 'mistral-small' ) ) {
					return 0;
				}
				return 1;
			}
			return 2;
		};

		$preference_groups = array_fill( 0, 3, array() );
		foreach ( $model_slugs as $model_slug ) {
			$group                         = $get_preference_group( $model_slug );
			$preference_groups[ $group ][] = $model_slug;
		}

		return array_merge( ...$preference_groups );
	}
}
