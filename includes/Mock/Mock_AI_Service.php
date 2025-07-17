<?php
/**
 * Class Felix_Arntz\AI_Services\Mock\Mock_AI_Service
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Mock;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;

/**
 * Class for a mock AI service.
 *
 * @since n.e.x.t
 */
class Mock_AI_Service extends Abstract_AI_Service {

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Service_Metadata $metadata The service metadata.
	 */
	public function __construct( Service_Metadata $metadata ) {
		$this->set_service_metadata( $metadata );
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
		$model_capability_groups = array(
			array(
				'model_prefix' => 'text-gen-',
				'num_models'   => 5,
				'capabilities' => array(
					AI_Capability::CHAT_HISTORY,
					AI_Capability::FUNCTION_CALLING,
					AI_Capability::MULTIMODAL_INPUT,
					AI_Capability::TEXT_GENERATION,
				),
			),
			array(
				'model_prefix' => 'image-gen-',
				'num_models'   => 2,
				'capabilities' => array(
					AI_Capability::IMAGE_GENERATION,
				),
			),
			array(
				'model_prefix' => 'other-',
				'num_models'   => 3,
				'capabilities' => array(),
			),
		);

		$models_data = array();
		foreach ( $model_capability_groups as $group ) {
			for ( $i = 1; $i <= $group['num_models']; $i++ ) {
				$model_slug = $group['model_prefix'] . $i;
				$model_caps = $group['capabilities'];

				$models_data[ $model_slug ] = Model_Metadata::from_array(
					array(
						'slug'         => $model_slug,
						'capabilities' => $model_caps,
					)
				);
			}
		}
		return $models_data;
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
				Mock_AI_Text_Generation_Model::class,
				Mock_AI_Image_Generation_Model::class,
			),
			$model_metadata->get_capabilities()
		);

		return new $model_class(
			$model_metadata,
			$model_params,
			$request_options
		);
	}
}
