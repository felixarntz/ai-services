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
	 * @since 0.3.0
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
	 * @since 0.3.0
	 *
	 * @param Response $response         The response instance. Must implement With_Stream. The response data will
	 *                                   be processed in chunks, with each chunk of data being passed to the process
	 *                                   callback.
	 * @param callable $process_callback The callback to process the response data. Receives the JSON-decoded response
	 *                                   data (associative array) as first parameter, and the previous processed data
	 *                                   as second parameter (or null in case this is the first chunk). It should
	 *                                   return the processed data for the chunk in the desired format.
	 * @return Generator Generator that yields the individual processed response data chunks.
	 *
	 * @throws Generative_AI_Exception If an error occurs while processing the response data.
	 */
	public function process_response_stream( Response $response, $process_callback ): Generator;

	/**
	 * Creates a new exception for an AI API request error.
	 *
	 * @since 0.3.0
	 *
	 * @param string $message The error message to include in the exception.
	 * @return Generative_AI_Exception The exception instance.
	 */
	public function create_request_exception( string $message ): Generative_AI_Exception;

	/**
	 * Creates a new exception for an AI API response error.
	 *
	 * @since 0.3.0
	 *
	 * @param string $message The error message to include in the exception.
	 * @return Generative_AI_Exception The exception instance.
	 */
	public function create_response_exception( string $message ): Generative_AI_Exception;

	/**
	 * Creates a new exception for an AI API response error for a missing key.
	 *
	 * @since 0.3.0
	 *
	 * @param string $key The missing key in the response data.
	 * @return Generative_AI_Exception The exception instance.
	 */
	public function create_missing_response_key_exception( string $key ): Generative_AI_Exception;
}
