<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Service_Registration_Context
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services;

use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;

/**
 * Value class with service context dependencies and data that can be used to create a service instance.
 *
 * @since n.e.x.t
 */
final class Service_Registration_Context {

	/**
	 * The service slug.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $slug;

	/**
	 * The service metadata.
	 *
	 * @since n.e.x.t
	 * @var Service_Metadata
	 */
	private $metadata;

	/**
	 * The service request handler instance.
	 *
	 * @since n.e.x.t
	 * @var Request_Handler
	 */
	private $request_handler;

	/**
	 * The service authentication instance, if any.
	 *
	 * @since n.e.x.t
	 * @var Authentication|null
	 */
	private $authentication;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string              $slug            The service slug.
	 * @param Service_Metadata    $metadata        The service metadata.
	 * @param Request_Handler     $request_handler The service request handler instance.
	 * @param Authentication|null $authentication  Optional. The service authentication instance, if any. Default null.
	 */
	public function __construct(
		string $slug,
		Service_Metadata $metadata,
		Request_Handler $request_handler,
		?Authentication $authentication = null
	) {
		$this->slug            = $slug;
		$this->metadata        = $metadata;
		$this->request_handler = $request_handler;
		$this->authentication  = $authentication;
	}

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets the service metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @return Service_Metadata The service metadata.
	 */
	public function get_metadata(): Service_Metadata {
		return $this->metadata;
	}

	/**
	 * Gets the service request handler instance.
	 *
	 * @since n.e.x.t
	 *
	 * @return Request_Handler The service request handler instance.
	 */
	public function get_request_handler(): Request_Handler {
		return $this->request_handler;
	}

	/**
	 * Gets the service authentication instance, if any.
	 *
	 * @since n.e.x.t
	 *
	 * @return Authentication|null The service authentication instance, if any.
	 */
	public function get_authentication(): ?Authentication {
		return $this->authentication;
	}
}
