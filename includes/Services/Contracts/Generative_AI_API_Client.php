<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;

/**
 * Interface for a class representing a client for a generative AI web API.
 *
 * @since 0.1.0
 */
interface Generative_AI_API_Client {

	/**
	 * Sends the given request to the API and returns the response data.
	 *
	 * @since 0.1.0
	 *
	 * @param Request $request The request instance.
	 * @return array<string, mixed> The response data.
	 *
	 * @throws Generative_AI_Exception If an error occurs while making the request.
	 */
	public function make_request( Request $request ): array;

	/**
	 * Creates a request instance to list the available models with their information.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $params          Optional. The request parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_list_models_request( array $params = array(), array $request_options = array() ): Request;

	/**
	 * Creates a request instance to generate content using the specified model.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_generate_content_request( string $model, array $params, array $request_options = array() ): Request;
}
