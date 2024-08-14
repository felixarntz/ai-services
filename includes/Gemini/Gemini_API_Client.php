<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Gemini\Gemini_API_Client
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Gemini;

use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_API_Client;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Exception\Request_Exception;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Get_Request;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\JSON_Post_Request;

/**
 * Class to interact directly with the Gemini API.
 *
 * @since n.e.x.t
 */
class Gemini_API_Client implements Generative_AI_API_Client {

	const DEFAULT_BASE_URL    = 'https://generativelanguage.googleapis.com';
	const DEFAULT_API_VERSION = 'v1beta';

	/**
	 * The Gemini API key.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $api_key;

	/**
	 * The HTTP instance to use for requests.
	 *
	 * @since n.e.x.t
	 * @var HTTP
	 */
	private $http;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $api_key The API key.
	 * @param HTTP   $http    The HTTP instance to use for requests.
	 */
	public function __construct( string $api_key, HTTP $http ) {
		$this->api_key = $api_key;
		$this->http    = $http;
	}

	/**
	 * Sends the given request to the API and returns the response data.
	 *
	 * @since n.e.x.t
	 *
	 * @param Request $request The request instance.
	 * @return array<string, mixed> The response data.
	 *
	 * @throws Generative_AI_Exception If an error occurs while making the request.
	 */
	public function make_request( Request $request ): array {
		try {
			$response = $this->http->request( $request );
		} catch ( Request_Exception $e ) {
			throw new Generative_AI_Exception(
				esc_html(
					sprintf(
						/* translators: %s: original error message */
						__( 'Error while making request to the Gemini API: %s ', 'wp-starter-plugin' ),
						$e->getMessage()
					)
				)
			);
		}

		if ( $response->get_status() < 200 || $response->get_status() >= 300 ) {
			$data = $response->get_data();
			if ( $data && isset( $data['error']['message'] ) ) {
				$error_message = $data['error']['message'];
			} else {
				$error_message = sprintf(
					/* translators: %d: HTTP status code */
					__( 'Bad status code: %d', 'wp-starter-plugin' ),
					$response->get_status()
				);
			}
			throw new Generative_AI_Exception(
				esc_html(
					sprintf(
						/* translators: %s: error message */
						__( 'Error while making request to the Gemini API: %s ', 'wp-starter-plugin' ),
						$error_message
					)
				)
			);
		}

		$data = $response->get_data();
		if ( ! $data ) {
			throw new Generative_AI_Exception(
				esc_html(
					sprintf(
						/* translators: %s: error message */
						__( 'Error while making request to the Gemini API: %s ', 'wp-starter-plugin' ),
						__( 'JSON response could not be decoded.', 'wp-starter-plugin' )
					)
				)
			);
		}

		return $data;
	}

	/**
	 * Creates a request instance to list the available models with their information.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $params          Optional. The request parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_list_models_request( array $params = array(), array $request_options = array() ): Request {
		return $this->create_get_request( 'models', $params, $request_options );
	}

	/**
	 * Creates a request instance to generate content using the specified model.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $model           The model name.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_generate_content_request( string $model, array $params, array $request_options = array() ): Request {
		if ( ! str_contains( $model, '/' ) ) {
			$model = 'models/' . $model;
		}
		return $this->create_post_request( "{$model}:generateContent", $params, $request_options );
	}

	/**
	 * Creates a GET request instance for the given parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	private function create_get_request( string $path, array $params, array $request_options = array() ): Request {
		return new Get_Request(
			$this->get_request_url( $path, $request_options ),
			$params,
			// TODO: Test if this works. Otherwise we'll need to add a ?key=... query parameter.
			$this->add_request_headers( $request_options )
		);
	}

	/**
	 * Creates a POST request instance for the given parameters.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	private function create_post_request( string $path, array $params, array $request_options = array() ): Request {
		return new JSON_Post_Request(
			$this->get_request_url( $path, $request_options ),
			$params,
			$this->add_request_headers( $request_options )
		);
	}

	/**
	 * Gets the request URL for the specified model and task.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string The request URL.
	 */
	private function get_request_url( string $path, array $request_options = array() ): string {
		$base_url    = $request_options['base_url'] ?? self::DEFAULT_BASE_URL;
		$api_version = $request_options['api_version'] ?? self::DEFAULT_API_VERSION;
		$path        = ltrim( $path, '/' );

		return "{$base_url}/{$api_version}/{$path}";
	}

	/**
	 * Adds the required request headers to the request options.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options The request options.
	 * @return array<string, mixed> The updated request options.
	 */
	private function add_request_headers( array $request_options ): array {
		if ( ! isset( $request_options['headers'] ) ) {
			$request_options['headers'] = array();
		}
		$request_options['headers']['X-Goog-Api-Client'] = 'wp-starter-plugin/' . WP_STARTER_PLUGIN_VERSION;
		$request_options['headers']['X-Goog-Api-Key']    = $this->api_key;
		return $request_options;
	}
}
