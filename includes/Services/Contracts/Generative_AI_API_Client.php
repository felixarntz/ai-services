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
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Response;
use Generator;

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
	 * @return Response The response instance.
	 *
	 * @throws Generative_AI_Exception If an error occurs while making the request.
	 */
	public function make_request( Request $request ): Response;

	/**
	 * Processes the response data from the API.
	 *
	 * @since n.e.x.t
	 *
	 * @param Response $response         The response instance. Must not be a stream response, i.e. not implement the
	 *                                   With_Stream interface.
	 * @param callable $process_callback The callback to process the response data. Receives the JSON-decoded response
	 *                                   data as associative array and should return the processed data in the desired
	 *                                   format.
	 * @return mixed The processed response data.
	 *
	 * @throws Generative_AI_Exception If an error occurs while processing the response data.
	 */
	public function process_response_data( Response $response, $process_callback );

	/**
	 * Processes the response data stream from the API.
	 *
	 * @since n.e.x.t
	 *
	 * @param Response $response         The response instance. Must implement With_Stream. The response data will
	 *                                   be processed in chunks, with each chunk of data being passed to the process
	 *                                   callback.
	 * @param callable $process_callback The callback to process the response data. Receives the JSON-decoded response
	 *                                   data as associative array and should return the processed data in the desired
	 *                                   format.
	 * @return Generator Generator that yields the individual processed response data chunks.
	 *
	 * @throws Generative_AI_Exception If an error occurs while processing the response data.
	 */
	public function process_response_stream( Response $response, $process_callback ): Generator;

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
