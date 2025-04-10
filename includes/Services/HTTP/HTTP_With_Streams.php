<?php
/**
 * Class Felix_Arntz\AI_Services\Services\HTTP\HTTP_With_Streams
 *
 * @since 0.3.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\HTTP;

use Felix_Arntz\AI_Services\Services\HTTP\Contracts\Stream_Request_Handler;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Response;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Exception\Request_Exception;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use Felix_Arntz\AI_Services_Dependencies\GuzzleHttp\Client;
use Felix_Arntz\AI_Services_Dependencies\GuzzleHttp\Exception\ClientException;

/**
 * Extended HTTP class with support for streaming responses.
 *
 * @since 0.3.0
 */
final class HTTP_With_Streams extends HTTP implements Stream_Request_Handler {

	/**
	 * Guzzle client instance.
	 *
	 * Used for streaming requests, as WordPress Core's Requests API does not support this.
	 *
	 * @since 0.3.0
	 * @var Client
	 */
	private $guzzle;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed> $default_options Optional. Default options to use for all requests. Default empty
	 *                                              array.
	 */
	public function __construct( array $default_options = array() ) {
		parent::__construct( $default_options );

		$this->guzzle = new Client();
	}

	/**
	 * Sends an HTTP request and streams the response.
	 *
	 * @since 0.3.0
	 *
	 * @param Request $request The request to send.
	 * @return Stream_Response The stream response.
	 *
	 * @throws Request_Exception Thrown if the request fails.
	 */
	public function request_stream( Request $request ): Stream_Response {
		$request_args = $this->build_request_args( $request );

		$request_options = array(
			'allow_redirects' => $request_args['options']['redirection'] > 0 ? array( 'max' => $request_args['options']['redirection'] ) : false,
			'timeout'         => (float) $request_args['options']['timeout'],
			'stream'          => true,
		);
		if ( isset( $request_args['data'] ) ) {
			if ( in_array( $request_args['type'], array( Request::HEAD, Request::GET, Request::DELETE ), true ) ) {
				$request_options['query'] = $request_args['data'];
			} else {
				if ( ! is_string( $request_args['data'] ) ) {
					$request_args['data'] = http_build_query( $request_args['data'], '', '&' );
				}
				$request_options['body'] = $request_args['data'];
			}
		}
		if ( isset( $request_args['headers'] ) ) {
			if ( ! isset( $request_args['headers']['User-Agent'] ) ) {
				$request_args['headers']['User-Agent'] = $request_args['options']['user-agent'];
			}
		} else {
			$request_args['headers'] = array(
				'User-Agent' => $request_args['options']['user-agent'],
			);
		}
		$request_options['headers'] = $request_args['headers'];

		try {
			$response = $this->guzzle->request(
				$request_args['type'],
				$request_args['url'],
				$request_options
			);
		} catch ( ClientException $e ) {
			throw new Request_Exception( esc_html( $e->getMessage() ) );
		}

		$headers = $this->sanitize_headers( $response->getHeaders() );

		return new Stream_Response( $response->getStatusCode(), $response->getBody(), $headers );
	}
}
