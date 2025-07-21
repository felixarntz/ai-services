<?php
/**
 * Class Felix_Arntz\AI_Services\XAI\XAI_AI_Service
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\XAI;

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
 * Class for the xAI AI service.
 *
 * @since n.e.x.t
 */
class XAI_AI_Service extends Abstract_AI_Service implements With_API_Client {
	use With_API_Client_Trait;

	const DEFAULT_API_BASE_URL = 'https://api.x.ai';
	const DEFAULT_API_VERSION  = 'v1';

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
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
				'xAI',
				$request_handler ?? new HTTP_With_Streams(),
				$authentication
			)
		);
	}

	/**
	 * Lists the available generative model slugs and their metadata.
	 *
	 * @since n.e.x.t
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

		// Unfortunately, the xAI API does not return model capabilities, so we have to hardcode them here.
		$grok_capabilities            = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::FUNCTION_CALLING,
			AI_Capability::TEXT_GENERATION,
			AI_Capability::WEB_SEARCH,
		);
		$grok_multimodal_capabilities = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::FUNCTION_CALLING,
			AI_Capability::MULTIMODAL_INPUT,
			AI_Capability::TEXT_GENERATION,
			AI_Capability::WEB_SEARCH,
		);
		$image_capabilities           = array(
			AI_Capability::IMAGE_GENERATION,
		);

		return array_reduce(
			$response_data['data'],
			static function ( array $models_data, array $model_data ) use ( $grok_capabilities, $grok_multimodal_capabilities, $image_capabilities ) {
				$model_slug = $model_data['id'];

				if ( str_starts_with( $model_slug, 'grok-' ) ) {
					if ( str_contains( $model_slug, '-image' ) ) {
						$model_caps = $image_capabilities;
					} elseif ( str_contains( $model_slug, '-vision' ) ) {
						$model_caps = $grok_multimodal_capabilities;
					} else {
						$model_caps = $grok_capabilities;
					}
				} else {
					$model_caps = array();
				}

				$models_data[ $model_slug ] = Model_Metadata::from_array(
					// The xAI API does not return a display name, so 'name' is omitted to auto-generate.
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
	 * @since n.e.x.t
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
				XAI_AI_Text_Generation_Model::class,
				XAI_AI_Image_Generation_Model::class,
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
	 * @since n.e.x.t
	 *
	 * @param string[] $model_slugs The model slugs to sort.
	 * @return string[] The model slugs, sorted by preference.
	 */
	protected function sort_models_by_preference( array $model_slugs ): array {
		// Prioritize latest, non-experimental models, preferring cheaper ones.
		$get_preference_group = static function ( $model_slug ) {
			if ( str_starts_with( $model_slug, 'grok-3' ) ) {
				if ( str_ends_with( $model_slug, '-mini' ) ) {
					return 0;
				}
				return 1;
			}
			if ( str_starts_with( $model_slug, 'grok-2' ) ) {
				return 2;
			}
			return 3;
		};

		$preference_groups = array_fill( 0, 4, array() );
		foreach ( $model_slugs as $model_slug ) {
			$group                         = $get_preference_group( $model_slug );
			$preference_groups[ $group ][] = $model_slug;
		}

		return array_merge( ...$preference_groups );
	}
}
