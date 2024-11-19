<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\Service_Generate_Text_REST_Route
 *
 * @since 0.3.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class representing the REST API route for generating text content.
 *
 * @since 0.3.0
 */
class Service_Generate_Text_REST_Route extends Service_Generate_Content_REST_Route {
	const BASE    = '/services/(?P<slug>[\w-]+):generate-text';
	const METHODS = WP_REST_Server::CREATABLE;

	/**
	 * Temporarily stores whether the request content needs a model that supports chat history.
	 *
	 * @since 0.3.0
	 * @var bool
	 */
	private $needs_chat_history = false;

	/**
	 * Returns the route base.
	 *
	 * @since 0.3.0
	 *
	 * @return string Route base.
	 */
	protected function base(): string {
		return self::BASE;
	}

	/**
	 * Returns the route methods, as a comma-separated string.
	 *
	 * @since 0.3.0
	 *
	 * @return string Route methods, as a comma-separated string.
	 */
	protected function methods(): string {
		return self::METHODS;
	}

	/**
	 * Handles the given request and returns a response.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 * @return WP_REST_Response WordPress REST response object.
	 *
	 * @throws REST_Exception Thrown when a REST error occurs.
	 */
	protected function handle_request( WP_REST_Request $request ): WP_REST_Response /* @phpstan-ignore-line */ {
		// Check if the request content is multi-turn chat history.
		if (
			is_array( $request['content'] ) &&
			count( $request['content'] ) > 1 &&
			isset( $request['content'][0] ) &&
			( $request['content'][0] instanceof Content || isset( $request['content'][0]['role'] ) )
		) {
			$this->needs_chat_history = true;
		} else {
			$this->needs_chat_history = false;
		}

		return parent::handle_request( $request );
	}

	/**
	 * Generates content using the given service and model.
	 *
	 * @since 0.3.0
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

		if ( $this->needs_chat_history && ! $model instanceof With_Chat_History ) {
			throw $this->create_missing_chat_history_exception();
		}

		return $model->generate_text( $content );
	}

	/**
	 * Retrieves the (text-based) model with the given slug and parameters.
	 *
	 * @since 0.3.0
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

		if ( $this->needs_chat_history && ! $model instanceof With_Chat_History ) {
			throw $this->create_missing_chat_history_exception();
		}

		return $model;
	}

	/**
	 * Processes the model parameters before retrieving the model with them.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 * @return array<string, mixed> The processed model parameters.
	 */
	protected function process_model_params( array $model_params ): array {
		/*
		 * At the very least, text generation capabilities need to be supported by the model.
		 * And if the request is for multi-turn content, chat history support is also required.
		 */
		if ( ! isset( $model_params['capabilities'] ) ) {
			$model_params['capabilities'] = array( AI_Capability::TEXT_GENERATION );
			if ( $this->needs_chat_history ) {
				$model_params['capabilities'][] = AI_Capability::CHAT_HISTORY;
			}
		}

		return parent::process_model_params( $model_params );
	}

	/**
	 * Creates a REST exception for a missing text generation support of a model.
	 *
	 * @since 0.3.0
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

	/**
	 * Creates a REST exception for a missing chat history support of a model.
	 *
	 * @since 0.3.0
	 *
	 * @return REST_Exception The REST exception.
	 */
	protected function create_missing_chat_history_exception(): REST_Exception {
		return REST_Exception::create(
			'rest_model_lacks_support',
			esc_html__( 'The model does not support chat history.', 'ai-services' ),
			400
		);
	}
}
