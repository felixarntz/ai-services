<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Chatbot\Chatbot_AI
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Chatbot;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Network_Env;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Site_Env;

/**
 * Class for the AI configuration powering the chatbot.
 *
 * @since n.e.x.t
 */
class Chatbot_AI {

	/**
	 * The site environment.
	 *
	 * @since n.e.x.t
	 * @var Site_Env
	 */
	private $site_env;

	/**
	 * The network environment.
	 *
	 * @since n.e.x.t
	 * @var Network_Env
	 */
	private $network_env;

	/**
	 * The current user instance.
	 *
	 * @since n.e.x.t
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Site_Env     $site_env    The site environment.
	 * @param Network_Env  $network_env The network environment.
	 * @param Current_User $current_user The current user instance.
	 */
	public function __construct( Site_Env $site_env, Network_Env $network_env, Current_User $current_user ) {
		$this->site_env     = $site_env;
		$this->network_env  = $network_env;
		$this->current_user = $current_user;
	}

	/**
	 * Gets the system instruction for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The system instruction.
	 */
	public function get_system_instruction(): string {
		// TODO: Use i18n functions.
		$instruction = 'You are a chatbot running inside a WordPress site.
You are here to help users with their questions and provide information.
You can also provide assistance with troubleshooting and technical issues.
The WordPress site URL is ' . $this->site_env->url( '/' ) . ' and the URL to the admin interface is ' . $this->site_env->admin_url( '/' ) . ".
You may also provide links to relevant sections of the WordPress admin interface, contextually for the site.
Only provide a link if it is relevant to the user's question or request.
Any links provided must not be contained within the message text itself, but separately at the very end of the message.
The link must be separated from the message text by three hyphens (---).
After the link, you must provide a brief call-to-action text (no more than 4 words, no punctuation) that explains what the user can do with the link.
This call-to-action test must be separated from the link by three hyphens (---).
For example: 'You can edit posts in the Posts screen. --- " . $this->site_env->admin_url( 'edit.php' ) . " --- View posts'.
Please provide the information in a clear and concise manner, and avoid using jargon or technical terms.
Do not provide any code snippets or technical details, unless specifically requested by the user.
Do not hallucinate or provide false information.
Here is some additional information about the WordPress site, so that you can help users more effectively:
 - The site is running on WordPress version " . $this->site_env->info( 'version' ) . '.
 - The primary language of the site is ' . $this->site_env->info( 'language' ) . '.
';

		$themes = $this->site_env->get_active_themes();
		if ( count( $themes ) === 2 ) {
			$instruction .= ' - The site is using the ' . $themes[1] . ' theme, with the ' . $themes[0] . ' child theme.' . "\n";
		} else {
			$instruction .= ' - The site is using the ' . $themes[0] . ' theme.' . "\n";
		}

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

		$current_user = $this->current_user->get();
		if ( $current_user->exists() ) {
			$wp_roles = wp_roles();
			if ( isset( $current_user->roles[0] ) && isset( $wp_roles->role_names[ $current_user->roles[0] ] ) ) {
				$role_name    = translate_user_role( $wp_roles->role_names[ $current_user->roles[0] ] );
				$instruction .= '- The current user has the role ' . $role_name . '.' . "\n";
			}
		} else {
			$instruction .= '- The current user is not logged in.' . "\n";
		}

		if ( $this->network_env->is_multisite() ) {
			$instruction .= '- The site is part of a multisite network.';
			if ( is_main_site() ) {
				$instruction .= ' It is the main site of the network.' . "\n";
			} else {
				$instruction .= ' It is a subsite of the network.' . "\n";
			}
			if ( $this->current_user->is_super_admin() ) {
				$instruction .= '  - The current user is a network administrator.' . "\n";
				$instruction .= '  - The URL to the network admin interface is ' . $this->network_env->admin_url( '/' ) . '.' . "\n";
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
		$active_plugins = $this->site_env->get_active_plugins();
		if ( $this->network_env->is_multisite() ) {
			$active_plugins = array_merge( $this->network_env->get_active_plugins(), $active_plugins );
		}
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
