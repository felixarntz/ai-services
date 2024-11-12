<?php
/**
 * Class Felix_Arntz\AI_Services\Services\HTTP\HTTP_With_Streams
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\HTTP;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Response;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Exception\Request_Exception;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use Felix_Arntz\AI_Services_Dependencies\GuzzleHttp\Client;
use Felix_Arntz\AI_Services_Dependencies\GuzzleHttp\Exception\ClientException;

/**
 * Extended HTTP class with support for streaming responses.
 *
 * @since n.e.x.t
 */
final class HTTP_With_Streams extends HTTP {

	/**
	 * Guzzle client instance.
	 *
	 * Used for streaming requests, as WordPress Core's Requests API does not support this.
	 *
	 * @since n.e.x.t
	 * @var Client
	 */
	private $guzzle;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
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
	 * @since n.e.x.t
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

	/**
	 * Sends an HTTP request and streams the response without relying on any third-party dependencies.
	 *
	 * @since n.e.x.t
	 *
	 * @param Request $request The request to send.
	 * @return Stream_Response The stream response.
	 *
	 * @throws Request_Exception Thrown if the request fails.
	 */
	public function experimental_request_stream( Request $request ): Stream_Response {
		$request_args = $this->build_request_args( $request );

		$stream_http_options = array(
			'method'          => $request_args['type'],
			'user_agent'      => $request_args['options']['user-agent'],
			'follow_location' => (int) $request_args['options']['redirection'] > 0 ? 1 : 0,
			'max_redirects'   => (int) $request_args['options']['redirection'],
			'timeout'         => (float) $request_args['options']['timeout'],
		);
		if ( isset( $request_args['data'] ) ) {
			if ( in_array( $request_args['type'], array( Request::HEAD, Request::GET, Request::DELETE ), true ) ) {
				if ( is_array( $request_args['data'] ) ) {
					$request_args['url'] = self::format_get( $request_args['url'], $request_args['data'] );
				}
				unset( $request_args['data'] );
			} else {
				if ( ! is_string( $request_args['data'] ) ) {
					$request_args['data'] = http_build_query( $request_args['data'], '', '&' );
				}
				$stream_http_options['content'] = $request_args['data'];
			}
		}
		if ( isset( $request_args['headers'] ) ) {
			// Does not support the expect header.
			unset( $request_args['headers']['Expect'], $request_args['headers']['expect'] );

			// Append a content-length header if body size is zero to match cURL's behavior.
			if ( ! isset( $stream_http_options['content'] ) || ! $stream_http_options['content'] ) {
				$request_args['headers']['Content-Length'] = '0';
			}

			$headers = array();
			foreach ( $request_args['headers'] as $header => $value ) {
				$headers[] = "{$header}: {$value}";
			}
			$stream_http_options['header'] = $headers;
		}

		$stream_ssl_options = array(
			'verify_peer'       => (bool) $request_args['options']['sslverify'],
			'capture_peer_cert' => (bool) $request_args['options']['sslverify'],
			'SNI_enabled'       => true,
			'cafile'            => $request_args['options']['sslcertificates'],
			'allow_self_signed' => ! $request_args['options']['sslverify'],
		);

		$stream_context = stream_context_create(
			array(
				'http' => $stream_http_options,
				'ssl'  => $stream_ssl_options,
			)
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.PHP.NoSilencedErrors.Discouraged
		$stream = @fopen( $request_args['url'], 'r', false, $stream_context );
		if ( false === $stream ) {
			throw new Request_Exception(
				esc_html(
					sprintf( 'Connection refused for URI %s', $request_args['url'] )
				)
			);
		}

		$timeout_sec  = (int) $request_args['options']['timeout'];
		$timeout_usec = ( $request_args['options']['timeout'] - $timeout_sec ) * 100000;
		stream_set_timeout( $stream, $timeout_sec, $timeout_usec );

		$stream = new Stream( $stream );

		$sink = $stream;
		if ( Request::HEAD !== $request_args['type'] ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.PHP.NoSilencedErrors.Discouraged
			$sink = @fopen( 'php://temp', 'r+' );
			if ( false === $sink ) {
				throw new Request_Exception( 'Cannot open sink in php://temp' );
			}
			$sink = new Stream( $sink );
		}

		if ( $sink !== $stream ) {
			$this->drain( $stream, $sink );
		}

		// TODO: How to get status and headers?
		return new Stream_Response( 200, $sink, array() );
	}

	/**
	 * Drains the source stream onto the sink.
	 *
	 * @since n.e.x.t
	 *
	 * @param Stream $source The source stream.
	 * @param Stream $sink   The sink stream.
	 */
	private function drain( Stream $source, Stream $sink ): void {
		while ( ! $source->eof() ) {
			if ( ! $sink->write( $source->read( 8192 ) ) ) {
				break;
			}
		}

		$sink->seek( 0 );
		$source->close();
	}

	/**
	 * Formats a URL given GET data.
	 *
	 * This is copied from the Requests::format_get() method.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $url  Original URL.
	 * @param array<string, mixed> $data Data to build query using.
	 * @return string URL with data.
	 */
	private static function format_get( string $url, array $data ): string {
		if ( ! empty( $data ) ) {
			$query = '';

			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$url_parts = parse_url( $url );
			if ( empty( $url_parts['query'] ) ) {
				$url_parts['query'] = '';
			} else {
				$query = $url_parts['query'];
			}

			$query .= '&' . http_build_query( $data, '', '&' );
			$query  = trim( $query, '&' );

			if ( empty( $url_parts['query'] ) ) {
				$url .= '?' . $query;
			} else {
				$url = str_replace( $url_parts['query'], $query, $url );
			}
		}

		return $url;
	}
}
