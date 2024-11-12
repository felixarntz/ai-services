<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\Service_Generate_Text_REST_Route
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use WP_REST_Server;

/**
 * Class representing the REST API route for generating text content.
 *
 * @since n.e.x.t
 */
class Service_Generate_Text_REST_Route extends Service_Generate_Content_REST_Route {
	const BASE    = '/services/(?P<slug>[\w-]+):generate-text';
	const METHODS = WP_REST_Server::CREATABLE;

	/**
	 * Returns the route base.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Route base.
	 */
	protected function base(): string {
		return self::BASE;
	}

	/**
	 * Returns the route methods, as a comma-separated string.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Route methods, as a comma-separated string.
	 */
	protected function methods(): string {
		return self::METHODS;
	}

	/**
	 * Generates content using the given service and model.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_Service          $service The service instance.
	 * @param Generative_AI_Model            $model   The model instance.
	 * @param string|Parts|Content|Content[] $content The content prompt.
	 * @return Candidates The generated content candidates.
	 *
	 * @throws REST_Exception Thrown when the model does not support text generation.
	 */
	protected function generate_content( Generative_AI_Service $service, Generative_AI_Model $model, $content ): Candidates {
		if ( ! $model instanceof With_Text_Generation ) {
			throw $this->create_missing_text_generation_exception();
		}

		return $model->generate_text( $content );
	}

	/**
	 * Retrieves the (text-based) model with the given slug and parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_Service $service      The service instance to get the model from.
	 * @param array<string, mixed>  $model_params The model parameters.
	 * @return Generative_AI_Model The model.
	 *
	 * @throws REST_Exception Thrown when the model cannot be retrieved or invalid parameters are provided.
	 */
	protected function get_model( Generative_AI_Service $service, array $model_params ): Generative_AI_Model {
		$model = parent::get_model( $service, $model_params );

		if ( ! $model instanceof With_Text_Generation ) {
			throw $this->create_missing_text_generation_exception();
		}

		return $model;
	}

	/**
	 * Processes the model parameters before retrieving the model with them.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 * @return array<string, mixed> The processed model parameters.
	 */
	protected function process_model_params( array $model_params ): array {
		// At the very least, text generation capabilities need to be supported by the model.
		if ( ! isset( $model_params['capabilities'] ) ) {
			$model_params['capabilities'] = array( AI_Capability::TEXT_GENERATION );
		}

		return parent::process_model_params( $model_params );
	}

	/**
	 * Creates a REST exception for a missing text generation support of a model.
	 *
	 * @since n.e.x.t
	 *
	 * @return REST_Exception The REST exception.
	 */
	protected function create_missing_text_generation_exception(): REST_Exception {
		return REST_Exception::create(
			'rest_model_lacks_support',
			esc_html__( 'The model does not support text generation.', 'ai-services' ),
			400
		);
	}
}
