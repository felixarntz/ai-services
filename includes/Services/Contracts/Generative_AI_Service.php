<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use InvalidArgumentException;

/**
 * Interface for a class representing a generative AI service which provides access to models.
 *
 * @since 0.1.0
 */
interface Generative_AI_Service {

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string;

	/**
	 * Gets the service metadata.
	 *
	 * @since 0.7.0
	 *
	 * @return Service_Metadata The service metadata.
	 */
	public function get_service_metadata(): Service_Metadata;

	/**
	 * Checks whether the service is connected.
	 *
	 * This is typically used to check whether the current service credentials are valid.
	 *
	 * @since 0.2.0
	 *
	 * @return bool True if the service is connected, false otherwise.
	 */
	public function is_connected(): bool;

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
	public function list_models( array $request_options = array() ): array;

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since 0.1.0
	 * @since 0.5.0 Support for the $tools and $toolConfig arguments was added.
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
	public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model;
}
