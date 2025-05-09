<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Service
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Base;

use Felix_Arntz\AI_Services\Services\API\Enums\Service_Type;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\Cache\Service_Request_Cache;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use InvalidArgumentException;
use RuntimeException;

/**
 * Base class for an AI service.
 *
 * @since n.e.x.t
 */
abstract class Abstract_AI_Service implements Generative_AI_Service {

	/**
	 * The service metadata.
	 *
	 * @since n.e.x.t
	 * @var Service_Metadata
	 */
	private $metadata;

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service slug.
	 */
	final public function get_service_slug(): string {
		return $this->get_service_metadata()->get_slug();
	}

	/**
	 * Gets the service metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @return Service_Metadata The service metadata.
	 *
	 * @throws RuntimeException Thrown if the service metadata is not set.
	 */
	final public function get_service_metadata(): Service_Metadata {
		if ( ! $this->metadata instanceof Service_Metadata ) {
			throw new RuntimeException( 'Service metadata must be set in the constructor.' );
		}

		return $this->metadata;
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
		if ( Service_Type::CLOUD !== $this->get_service_metadata()->get_type() ) {
			return true;
		}

		try {
			$this->list_models();
			return true;
		} catch ( Generative_AI_Exception $e ) {
			return false;
		}
	}

	// phpcs:disable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params    {
	 *     Optional. Model parameters. Default empty array.
	 *
	 *     @type string                 $feature           Required. Unique identifier of the feature that the model
	 *                                                     will be used for. Must only contain lowercase letters,
	 *                                                     numbers, hyphens.
	 *     @type string                 $model             The model slug. By default, the model will be determined
	 *                                                     based on heuristics such as the requested capabilities.
	 *     @type string[]               $capabilities      Capabilities requested for the model to support. It is
	 *                                                     recommended to specify this if you do not explicitly specify
	 *                                                     a model slug.
	 *     @type Tools|null             $tools             The tools to use for the model. Default none.
	 *     @type Tool_Config|null       $toolConfig        Tool configuration options. Default none.
	 *     @type Generation_Config|null $generationConfig  Model generation configuration options. Default none.
	 *     @type string|Parts|Content   $systemInstruction The system instruction for the model. Default none.
	 * }
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 * @throws Generative_AI_Exception Thrown if getting the model fails.
	 */
	final public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		$models_metadata = $this->cached_list_models( $request_options );

		if ( isset( $model_params['model'] ) ) {
			$model = $model_params['model'];
			unset( $model_params['model'] );
		} else {
			if ( isset( $model_params['capabilities'] ) ) {
				$model_slugs = AI_Capabilities::get_model_slugs_for_capabilities(
					$models_metadata,
					$model_params['capabilities']
				);
			} else {
				$model_slugs = array_keys( $models_metadata );
			}
			$model = $this->sort_models_by_preference( $model_slugs )[0];
		}

		if ( ! isset( $models_metadata[ $model ] ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid model slug "%1$s" for the service "%2$s".',
					htmlspecialchars( $model ), // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					htmlspecialchars( $this->get_service_slug() ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}

		$model_metadata = $models_metadata[ $model ];

		return $this->create_model_instance( $model_metadata, $model_params, $request_options );
	}

	// phpcs:enable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber

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
	abstract protected function create_model_instance( Model_Metadata $model_metadata, array $model_params, array $request_options ): Generative_AI_Model;

	/**
	 * Sorts model slugs by preference.
	 *
	 * @since n.e.x.t
	 *
	 * @param string[] $model_slugs The model slugs to sort.
	 * @return string[] The model slugs, sorted by preference.
	 */
	protected function sort_models_by_preference( array $model_slugs ): array {
		// By default, no sorting is applied.
		return $model_slugs;
	}

	/**
	 * Sets the service metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @param Service_Metadata $metadata The service metadata.
	 */
	final protected function set_service_metadata( Service_Metadata $metadata ): void {
		$this->metadata = $metadata;
	}

	/**
	 * Lists the available generative model slugs and their metadata, wrapped in a transient cache.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return array<string, Model_Metadata> Metadata for each model, mapped by model slug.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	private function cached_list_models( array $request_options = array() ): array {
		if ( ! function_exists( 'get_transient' ) ) {
			// If the transient function is not available, we cannot cache the result.
			return $this->list_models( $request_options );
		}

		return Service_Request_Cache::wrap_transient(
			$this->get_service_slug(),
			array( $this, 'list_models' ),
			array( $request_options )
		);
	}
}
