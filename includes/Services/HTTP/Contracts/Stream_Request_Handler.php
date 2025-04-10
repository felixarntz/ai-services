<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\HTTP\Contracts\Stream_Request_Handler
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\HTTP\Contracts;

use Felix_Arntz\AI_Services\Services\HTTP\Stream_Response;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Exception\Request_Exception;

/**
 * Interface for a request handler that can stream responses.
 *
 * @since n.e.x.t
 */
interface Stream_Request_Handler {

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
	public function request_stream( Request $request ): Stream_Response;
}
