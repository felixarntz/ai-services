<?php
/**
 * Class Felix_Arntz\AI_Services\Chatbot\Chatbot_Loader
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Chatbot;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\Services_API;

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
		 * The chatbot is the only user-facing feature built into this plugin, effectively as a small proof of concept,
		 * since otherwise it is an infrastructure plugin. It is disabled by default, but can be enabled by using this
		 * filter.
		 *
		 * @since 0.1.0
		 * @since 0.2.0 The default value was changed to false.
		 *
		 * @param bool $enabled Whether the chatbot feature should be enabled or not.
		 */
		$enabled = (bool) apply_filters( 'ai_services_chatbot_enabled', false );
		if ( ! $enabled ) {
			return false;
		}

		return $this->services_api->has_available_services(
			array( 'capabilities' => array( AI_Capability::TEXT_GENERATION, AI_Capability::CHAT_HISTORY ) )
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
