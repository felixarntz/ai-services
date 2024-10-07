<?php
/**
 * Class Felix_Arntz\AI_Services\Chatbot\Chatbot_Loader
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Chatbot;

use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;

/**
 * Class responsible for loading the Google AI-powered chatbot.
 *
 * @since 0.1.0
 */
class Chatbot_Loader {

	/**
	 * Services API instance.
	 *
	 * @since 0.1.0
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Services_API $services_api The services API instance.
	 */
	public function __construct( Services_API $services_api ) {
		$this->services_api = $services_api;
	}

	/**
	 * Checks if the chatbot can be loaded.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if the chatbot can be loaded, false otherwise.
	 */
	public function can_load(): bool {
		/**
		 * Filters whether the chatbot is enabled.
		 *
		 * The chatbot is the only user-facing feature of this plugin, effectively as a small proof of concept, since
		 * otherwise it is an infrastructure plugin. As such, this filter can be used to disable the chatbot.
		 *
		 * @since 0.1.0
		 *
		 * @param bool $enabled Whether the chatbot feature should be enabled or not.
		 */
		$enabled = (bool) apply_filters( 'ai_services_chatbot_enabled', true );
		if ( ! $enabled ) {
			return false;
		}

		return $this->services_api->has_available_services(
			array( 'capabilities' => array( AI_Capabilities::CAPABILITY_TEXT_GENERATION ) )
		);
	}

	/**
	 * Loads the chatbot using the given instance.
	 *
	 * @since 0.1.0
	 *
	 * @param Chatbot $chatbot The chatbot instance.
	 */
	public function load( Chatbot $chatbot ): void {
		$chatbot->add_hooks();
	}
}
