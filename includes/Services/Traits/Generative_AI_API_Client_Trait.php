<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\Generative_AI_API_Client_Trait
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Exception\Request_Exception;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;

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
	 * @return array<string, mixed> The response data.
	 *
	 * @throws Generative_AI_Exception If an error occurs while making the request.
	 */
	final public function make_request( Request $request ): array {
		try {
			$response = $this->get_http()->request( $request );
		} catch ( Request_Exception $e ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->create_request_exception( $e->getMessage() );
		}

		if ( $response->get_status() < 200 || $response->get_status() >= 300 ) {
			$data = $response->get_data();
			if ( $data && isset( $data['error']['message'] ) ) {
				$error_message = $data['error']['message'];
			} else {
				$error_message = sprintf(
					/* translators: %d: HTTP status code */
					__( 'Bad status code: %d', 'ai-services' ),
					$response->get_status()
				);
			}
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->create_request_exception( $error_message );
		}

		$data = $response->get_data();
		if ( ! $data ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->create_request_exception( __( 'JSON response could not be decoded.', 'ai-services' ) );
		}

		return $data;
	}

	/**
	 * Creates a new exception for an AI API request error.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message The error message to include in the exception.
	 * @return Generative_AI_Exception The exception instance.
	 */
	final protected function create_request_exception( string $message ): Generative_AI_Exception {
		return new Generative_AI_Exception(
			esc_html(
				sprintf(
					/* translators: 1: API name, 2: error message */
					__( 'Error while making request to the %1$s API: %2$s ', 'ai-services' ),
					$this->get_api_name(),
					$message
				)
			)
		);
	}

	/**
	 * Adds default options to the given request.
	 *
	 * @since 0.1.0
	 *
	 * @param Request $request The request instance to add the options to.
	 */
	final protected function add_default_options( Request $request ): void {
		$options = $request->get_options();
		if ( ! isset( $options['timeout'] ) ) {
			$request->add_option( 'timeout', 15 );
		}
	}

	/**
	 * Returns the HTTP instance to use for requests.
	 *
	 * @since 0.1.0
	 *
	 * @return HTTP The HTTP instance.
	 */
	abstract protected function get_http(): HTTP;

	/**
	 * Returns the human readable API name (without the "API" suffix).
	 *
	 * @since 0.1.0
	 *
	 * @return string The API name.
	 */
	abstract protected function get_api_name(): string;
}
