<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Generative_AI_API_Client_Trait
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\HTTP\Contracts\Stream_Request_Handler;
use Felix_Arntz\AI_Services\Services\HTTP\Contracts\With_Stream;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Response;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Exception\Request_Exception;
use Generator;
use InvalidArgumentException;

/**
 * Trait for an API client class which implements the Generative_AI_API_Client interface.
 *
 * @since 0.1.0
 */
trait Generative_AI_API_Client_Trait {

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
	final public function make_request( Request $request ): Response {
		$request_handler = $this->get_request_handler();

		$options = $request->get_options();
		if ( isset( $options['stream'] ) && $options['stream'] ) {
			if ( ! $request_handler instanceof Stream_Request_Handler ) {
				throw new Generative_AI_Exception(
					'Streaming requests are not supported by this API client.'
				);
			}

			try {
				$response = $request_handler->request_stream( $request );
			} catch ( Request_Exception $e ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				throw $this->create_request_exception( $e->getMessage() );
			}
		} else {
			try {
				$response = $request_handler->request( $request );
			} catch ( Request_Exception $e ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				throw $this->create_request_exception( $e->getMessage() );
			}
		}

		if ( $response->get_status() < 200 || $response->get_status() >= 300 ) {
			$data = $response->get_data();
			if ( $data && isset( $data['error']['message'] ) && is_string( $data['error']['message'] ) ) {
				$error_message = $data['error']['message'];
			} elseif ( $data && isset( $data['error'] ) && is_string( $data['error'] ) ) {
				$error_message = $data['error'];
			} elseif ( $data && isset( $data['message'] ) && is_string( $data['message'] ) ) {
				$error_message = $data['message'];
			} else {
				$error_message = sprintf(
					'Bad status code: %d',
					$response->get_status()
				);
			}
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->create_request_exception( $error_message );
		}

		return $response;
	}

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
	final public function process_response_data( Response $response, $process_callback ) {
		if ( $response instanceof With_Stream ) {
			throw new Generative_AI_Exception(
				sprintf(
					'Response must not implement %s.',
					With_Stream::class
				)
			);
		}

		$data = $response->get_data();
		if ( ! $data ) {
			throw new Generative_AI_Exception(
				'No data received in response.'
			);
		}

		$processed_data = call_user_func( $process_callback, $data );
		if ( ! $processed_data ) {
			throw new Generative_AI_Exception(
				'No data returned by process callback.'
			);
		}

		return $processed_data;
	}

	/**
	 * Processes the response body from the API.
	 *
	 * @since 0.7.0
	 *
	 * @param Response $response         The response instance. Must not be a stream response, i.e. not implement the
	 *                                   With_Stream interface.
	 * @param callable $process_callback The callback to process the response body. Receives the response body as
	 *                                   string and should return the processed data in the desired format.
	 * @return mixed The processed response data.
	 *
	 * @throws Generative_AI_Exception If an error occurs while processing the response body.
	 */
	final public function process_response_body( Response $response, $process_callback ) {
		if ( $response instanceof With_Stream ) {
			throw new Generative_AI_Exception(
				sprintf(
					'Response must not implement %s.',
					With_Stream::class
				)
			);
		}

		$body = $response->get_body();
		if ( ! $body ) {
			throw new Generative_AI_Exception(
				'No body received in response.'
			);
		}

		$processed_data = call_user_func( $process_callback, $body );
		if ( ! $processed_data ) {
			throw new Generative_AI_Exception(
				'No data returned by process callback.'
			);
		}

		return $processed_data;
	}

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
	final public function process_response_stream( Response $response, $process_callback ): Generator {
		if ( ! $response instanceof With_Stream ) {
			throw new Generative_AI_Exception(
				sprintf(
					'Response does not implement %s.',
					With_Stream::class
				)
			);
		}

		$stream_generator = $response->read_stream();

		$previous_processed_data = null;
		foreach ( $stream_generator as $data ) {
			$processed_data = call_user_func( $process_callback, $data, $previous_processed_data );
			if ( ! $processed_data ) {
				continue;
			}

			$previous_processed_data = $processed_data;
			yield $processed_data;
		}
	}

	/**
	 * Creates a new exception for a bad request, i.e. invalid or unsupported request data.
	 *
	 * @since 0.7.0
	 *
	 * @param string $message The error message to include in the exception.
	 * @return InvalidArgumentException The exception instance.
	 */
	final public function create_bad_request_exception( string $message ): InvalidArgumentException {
		return new InvalidArgumentException(
			sprintf(
				'Invalid request data for the %1$s API: %2$s',
				$this->get_api_name(),
				$message
			)
		);
	}

	/**
	 * Creates a new exception for an AI API request error.
	 *
	 * @since 0.1.0
	 * @since 0.3.0 Method made public.
	 *
	 * @param string $message The error message to include in the exception.
	 * @return Generative_AI_Exception The exception instance.
	 */
	final public function create_request_exception( string $message ): Generative_AI_Exception {
		return new Generative_AI_Exception(
			sprintf(
				'Error while making request to the %1$s API: %2$s ',
				$this->get_api_name(),
				$message
			)
		);
	}

	/**
	 * Creates a new exception for an AI API response error.
	 *
	 * @since 0.3.0
	 *
	 * @param string $message The error message to include in the exception.
	 * @return Generative_AI_Exception The exception instance.
	 */
	final public function create_response_exception( string $message ): Generative_AI_Exception {
		return new Generative_AI_Exception(
			sprintf(
				'Error in the response from the %1$s API: %2$s ',
				$this->get_api_name(),
				$message
			)
		);
	}

	/**
	 * Creates a new exception for an AI API response error for a missing key.
	 *
	 * @since 0.3.0
	 *
	 * @param string $key The missing key in the response data.
	 * @return Generative_AI_Exception The exception instance.
	 */
	final public function create_missing_response_key_exception( string $key ): Generative_AI_Exception {
		return $this->create_response_exception(
			sprintf(
				'The response is missing the "%s" key.',
				$key
			)
		);
	}

	/**
	 * Returns the request handler instance to use for requests.
	 *
	 * @since 0.1.0
	 * @since 0.6.0 Renamed from `get_http()`.
	 *
	 * @return Request_Handler The request handler instance.
	 */
	abstract protected function get_request_handler(): Request_Handler;

	/**
	 * Returns the human readable API name (without the "API" suffix).
	 *
	 * @since 0.1.0
	 *
	 * @return string The API name.
	 */
	abstract protected function get_api_name(): string;
}
