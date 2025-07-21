<?php
/**
 * Class Felix_Arntz\AI_Services\Perplexity\Perplexity_AI_Service
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Perplexity;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Service;
use Felix_Arntz\AI_Services\Services\Base\Generic_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\HTTP\HTTP_With_Streams;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;
use RuntimeException;

/**
 * Class for the Perplexity AI service.
 *
 * @since n.e.x.t
 */
class Perplexity_AI_Service extends Abstract_AI_Service implements With_API_Client {
	use With_API_Client_Trait;

	const DEFAULT_API_BASE_URL = 'https://api.perplexity.ai';
	const DEFAULT_API_VERSION  = '';

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
				'Perplexity',
				$request_handler ?? new HTTP_With_Streams(),
				$authentication
			)
		);
	}

	/**
	 * Checks whether the service is connected.
	 *
	 * In case of a cloud based service, this is typically used to check whether the current service credentials are
	 * valid. For other service types, this may check other requirements, or simply return true.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the service is connected, false otherwise.
	 *
	 * @throws RuntimeException Thrown if the connection check cannot be performed.
	 */
	public function is_connected(): bool {
		/*
		 * To check the connection, the only way is to generate content.
		 * In order to avoid unnecessary API usage, we generate a single token of text.
		 */
		$model = $this->get_model(
			array(
				'feature'          => 'connection_check',
				'capabilities'     => array( AI_Capability::TEXT_GENERATION ),
				'generationConfig' => new Text_Generation_Config( array( 'maxOutputTokens' => 1 ) ),
			)
		);

		// This should never happen but needs to be here as a sanity check.
		if ( ! $model instanceof With_Text_Generation ) {
			throw new RuntimeException( 'No Anthropic model supports text generation.' );
		}

		try {
			$model->generate_text( 'a' );
			return true;
		} catch ( Generative_AI_Exception $e ) {
			return false;
		}
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
		$perplexity_capabilities            = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::TEXT_GENERATION,
			AI_Capability::WEB_SEARCH, // Always enabled for Perplexity.
		);
		$perplexity_multimodal_capabilities = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::MULTIMODAL_INPUT,
			AI_Capability::TEXT_GENERATION,
			AI_Capability::WEB_SEARCH, // Always enabled for Perplexity.
		);

		/*
		 * Perplexity's API does not provide a direct endpoint to list models.
		 * Instead, we define the models and their capabilities based on Perplexity's documentation:
		 * https://docs.perplexity.ai/models/model-cards
		 */
		$models = array(
			'sonar-pro',
			'sonar',
			'sonar-deep-research',
			'sonar-reasoning-pro',
			'sonar-reasoning',
		);

		$models_metadata = array();
		foreach ( $models as $slug ) {
			if ( str_contains( $slug, 'deep-research' ) ) {
				$model_caps = $perplexity_capabilities;
			} else {
				$model_caps = $perplexity_multimodal_capabilities;
			}
			$models_metadata[ $slug ] = Model_Metadata::from_array(
				array(
					'slug'         => $slug,
					'capabilities' => $model_caps,
				)
			);
		}
		return $models_metadata;
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
		return new Perplexity_AI_Text_Generation_Model(
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
		$get_preference_group = static function ( $model_slug ) {
			if ( 'sonar' === $model_slug ) {
				return 0;
			}
			if ( 'sonar-pro' === $model_slug ) {
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
