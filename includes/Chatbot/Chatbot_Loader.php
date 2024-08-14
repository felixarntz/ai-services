<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Chatbot\Chatbot_Loader
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Chatbot;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option;

/**
 * Class responsible for loading the Gemini AI-powered chatbot.
 *
 * @since n.e.x.t
 */
class Chatbot_Loader {

	/**
	 * The current user.
	 *
	 * @since n.e.x.t
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * The Gemini API key option.
	 *
	 * @since n.e.x.t
	 * @var Option
	 */
	private $api_key;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Current_User $current_user The current user.
	 * @param Option       $api_key      The Gemini API key option.
	 */
	public function __construct( Current_User $current_user, Option $api_key ) {
		$this->current_user = $current_user;
		$this->api_key      = $api_key;
	}

	/**
	 * Checks if the chatbot can be loaded.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the chatbot can be loaded, false otherwise.
	 */
	public function can_load(): bool {
		if ( ! $this->api_key->get_value() ) {
			return false;
		}

		if ( ! $this->current_user->is_logged_in() ) {
			return false;
		}

		if ( ! $this->current_user->has_cap( 'wpsp_access_services' ) ) {
			return false;
		}

		return true;
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
