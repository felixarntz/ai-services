<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Chatbot\Chatbot_AI
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Chatbot;

use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidates;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Parts\Text_Part;

/**
 * Class for the AI configuration powering the chatbot.
 *
 * @since n.e.x.t
 */
class Chatbot_AI {

	/**
	 * The AI service instance.
	 *
	 * @since n.e.x.t
	 * @var Generative_AI_Service
	 */
	private $service;

	/**
	 * The generative model.
	 *
	 * @since n.e.x.t
	 * @var Generative_AI_Model|null
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_Service $service The AI instance.
	 */
	public function __construct( Generative_AI_Service $service ) {
		$this->service = $service;
	}

	/**
	 * Get the generative model for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generative_AI_Model The generative model.
	 */
	public function get_model(): Generative_AI_Model {
		if ( null === $this->model ) {
			// TODO: Support services other than Google.
			$this->model = $this->service->get_model(
				'gemini-1.5-flash',
				array( 'system_instruction' => $this->get_system_instruction() )
			);
		}
		return $this->model;
	}

	/**
	 * Gets the text from the given response of the generative model.
	 *
	 * The first candidate in the response is used, and its text parts are concatenated.
	 *
	 * @since n.e.x.t
	 *
	 * @param Candidates $candidates The candidates response from the generative model.
	 * @return string The text.
	 *
	 * @throws Generative_AI_Exception If the response does not include any text parts.
	 */
	public function get_text_from_candidates( Candidates $candidates ): string {
		$candidates = $candidates->filter( array( 'part_class_name' => Text_Part::class ) );
		if ( count( $candidates ) === 0 ) {
			throw new Generative_AI_Exception(
				esc_html__( 'The response from the AI service does not include any text parts.', 'wp-starter-plugin' )
			);
		}

		$parts = $candidates->get( 0 )->get_content()->get_parts();

		$text_parts = array();
		foreach ( $parts as $part ) {
			$text_parts[] = trim( $part->to_array()['text'] );
		}

		return implode( "\n\n", $text_parts );
	}

	/**
	 * Get the system instruction for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The system instruction.
	 */
	private function get_system_instruction(): string {
		$instruction = 'You are a chatbot running inside a WordPress site.
You are here to help users with their questions and provide information.
You can also provide assistance with troubleshooting and technical issues.
The WordPress site URL is ' . home_url( '/' ) . ' and the URL to the admin interface is ' . admin_url( '/' ) . ".
You may also provide links to relevant sections of the WordPress admin interface, contextually for the site.
Any links provided must not be contained within the message text itself, but separately at the very end of the message.
The link must be separated from the message text by three hyphens (---).
For example: 'You can edit posts in the Posts screen. --- " . admin_url( 'edit.php' ) . "'.
Please provide the information in a clear and concise manner, and avoid using jargon or technical terms.
Do not provide any code snippets or technical details, unless specifically requested by the user.
Do not hallucinate or provide false information.
Here is some additional information about the WordPress site, so that you can help users more effectively:
 - The site is running on WordPress version " . get_bloginfo( 'version' ) . '.
 - The primary locale of the site is ' . get_locale() . '.
 - The site is using the ' . get_template() . ' theme.
';

		if ( wp_is_block_theme() ) {
			$instruction .= ' - The theme is a block theme.' . "\n";
		} else {
			$instruction .= ' - The theme is a classic theme.' . "\n";
		}

		$active_plugins_info = $this->get_active_plugins_info();
		if ( count( $active_plugins_info ) > 0 ) {
			$instruction .= '- The following plugins are active on the site:' . "\n";
			$instruction .= '  - ' . implode( "\n  - ", $active_plugins_info ) . "\n";
		} else {
			$instruction .= '- No plugins are active on the site.' . "\n";
		}

		$current_user = wp_get_current_user();
		if ( $current_user->exists() ) {
			$wp_roles = wp_roles();
			if ( isset( $current_user->roles[0] ) && isset( $wp_roles->role_names[ $current_user->roles[0] ] ) ) {
				$role_name    = translate_user_role( $wp_roles->role_names[ $current_user->roles[0] ] );
				$instruction .= '- The current user has the role ' . $role_name . '.' . "\n";
			}
		} else {
			$instruction .= '- The current user is not logged in.' . "\n";
		}

		if ( is_multisite() ) {
			$instruction .= '- The site is part of a multisite network.';
			if ( is_main_site() ) {
				$instruction .= ' It is the main site of the network.' . "\n";
			} else {
				$instruction .= ' It is a subsite of the network.' . "\n";
			}
			if ( is_super_admin() ) {
				$instruction .= '  - The current user is a network administrator.' . "\n";
				$instruction .= '  - The URL to the network admin interface is ' . network_admin_url( '/' ) . '.' . "\n";
			}
		}

		return $instruction;
	}

	/**
	 * Get information about the active plugins on the site.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] List of plain text strings with information about the active plugins (name, version, and link URL).
	 */
	private function get_active_plugins_info(): array {
		$active_plugins = wp_get_active_and_valid_plugins();
		if ( count( $active_plugins ) === 0 ) {
			return array();
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();

		return array_map(
			function ( $plugin ) use ( $plugins ) {
				if ( ! isset( $plugins[ $plugin ] ) ) {
					return sprintf(
						'Unknown plugin %s',
						trim( dirname( $plugin ), '/' )
					);
				}

				if ( isset( $plugins[ $plugin ]['Version'] ) && isset( $plugins[ $plugin ]['slug'] ) ) {
					return sprintf(
						'%1$s (version %2$s), see %3$s',
						$plugins[ $plugin ]['Name'],
						$plugins[ $plugin ]['Version'],
						__( 'https://wordpress.org/plugins/', 'default' ) . $plugins[ $plugin ]['slug'] . '/'
					);
				}

				if ( isset( $plugins[ $plugin ]['Version'] ) ) {
					return sprintf(
						'%1$s (version %2$s)',
						$plugins[ $plugin ]['Name'],
						$plugins[ $plugin ]['Version']
					);
				}

				if ( isset( $plugins[ $plugin ]['slug'] ) ) {
					return sprintf(
						'%1$s, see %2$s',
						$plugins[ $plugin ]['Name'],
						__( 'https://wordpress.org/plugins/', 'default' ) . $plugins[ $plugin ]['slug'] . '/'
					);
				}

				return $plugins[ $plugin ]['Name'];
			},
			$active_plugins
		);
	}
}
