<?php
/**
 * Interface Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Contracts;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Content;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Parts;
use Vendor_NS\WP_Starter_Plugin\Services\Util\AI_Capabilities;

/**
 * Interface for a class representing a generative AI service which provides access to models.
 *
 * @since n.e.x.t
 */
interface Generative_AI_Service {

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string;

	/**
	 * Gets the list of AI capabilities that the service and its models support.
	 *
	 * @since n.e.x.t
	 * @see AI_Capabilities
	 *
	 * @return string[] The list of AI capabilities.
	 */
	public function get_capabilities(): array;

	/**
	 * Lists the available generative model slugs.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string[] The available model slugs.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function list_models( array $request_options = array() ): array;

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    {
	 *     Optional. Additional model parameters. Default empty array.
	 *
	 *     @type array<string, mixed> $generation_config  Optional. Model generation configuration options. Default
	 *                                                    empty array.
	 *     @type string|Parts|Content $system_instruction Optional. The system instruction for the model. Default none.
	 * }
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 * @throws Generative_AI_Exception Thrown if getting the model fails.
	 */
	public function get_model( string $model, array $model_params = array(), array $request_options = array() ): Generative_AI_Model;
}
