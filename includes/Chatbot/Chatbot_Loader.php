<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Chatbot\Chatbot_Loader
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Chatbot;

use Vendor_NS\WP_Starter_Plugin\Services\Services_API;

/**
 * Class responsible for loading the Google AI-powered chatbot.
 *
 * @since n.e.x.t
 */
class Chatbot_Loader {

	/**
	 * Services API instance.
	 *
	 * @since n.e.x.t
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Services_API $services_api The services API instance.
	 */
	public function __construct( Services_API $services_api ) {
		$this->services_api = $services_api;
	}

	/**
	 * Checks if the chatbot can be loaded.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the chatbot can be loaded, false otherwise.
	 */
	public function can_load(): bool {
		return $this->services_api->is_service_available( 'google' );
	}

	/**
	 * Loads the chatbot using the given instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param Chatbot $chatbot The chatbot instance.
	 */
	public function load( Chatbot $chatbot ): void {
		$chatbot->add_hooks();
	}
}
