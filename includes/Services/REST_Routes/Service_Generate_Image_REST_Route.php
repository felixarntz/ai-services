<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\Service_Generate_Image_REST_Route
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History;
use Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class representing the REST API route for generating text content.
 *
 * @since n.e.x.t
 */
class Service_Generate_Image_REST_Route extends Service_Generate_Content_REST_Route {
	const BASE    = '/services/(?P<slug>[\w-]+):generate-image';
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
		if ( ! $model instanceof With_Image_Generation ) {
			throw $this->create_missing_image_generation_exception();
		}

		return $model->generate_image( $content );
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

		if ( ! $model instanceof With_Image_Generation ) {
			throw $this->create_missing_image_generation_exception();
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
		// At the very least, image generation capabilities need to be supported by the model.
		if ( ! isset( $model_params['capabilities'] ) ) {
			$model_params['capabilities'] = array( AI_Capability::IMAGE_GENERATION );
		}

		// Replace parent method implementation because parameters are quite different for image generation.
		if ( isset( $model_params['generationConfig'] ) && is_array( $model_params['generationConfig'] ) ) {
			$model_params['generationConfig'] = Image_Generation_Config::from_array( $model_params['generationConfig'] );
		}

		if ( isset( $model_params['systemInstruction'] ) && is_array( $model_params['systemInstruction'] ) ) {
			if ( isset( $model_params['systemInstruction']['role'] ) ) {
				$model_params['systemInstruction'] = Content::from_array( $model_params['systemInstruction'] );
			} else {
				$model_params['systemInstruction'] = Parts::from_array( $model_params['systemInstruction'] );
			}
		}

		return $model_params;
	}

	/**
	 * Creates a REST exception for a missing text generation support of a model.
	 *
	 * @since n.e.x.t
	 *
	 * @return REST_Exception The REST exception.
	 */
	protected function create_missing_image_generation_exception(): REST_Exception {
		return REST_Exception::create(
			'rest_model_lacks_support',
			esc_html__( 'The model does not support image generation.', 'ai-services' ),
			400
		);
	}

	/**
	 * Returns the route specific arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Route arguments.
	 */
	protected function args(): array {
		$args = parent::args();

		// Override generation config to be specific to image generation.
		$args['modelParams']['generationConfig'] = array_merge(
			array( 'description' => __( 'Model generation configuration options.', 'ai-services' ) ),
			Image_Generation_Config::get_json_schema()
		);

		// Tools are not supported for image generation.
		unset( $args['modelParams']['tools'], $args['modelParams']['toolConfig'] );

		return $args;
	}
}
