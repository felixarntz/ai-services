<?php
/**
 * Class Felix_Arntz\AI_Services\Chatbot\Chatbot_AI
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Chatbot;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Network_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Site_Env;

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
		$instruction  = __( 'You are a chatbot running inside a WordPress site.', 'ai-services' ) . "\n";
		$instruction .= __( 'You are here to help users with their questions and provide information.', 'ai-services' ) . "\n";
		$instruction .= __( 'You can also provide assistance with troubleshooting and technical issues.', 'ai-services' ) . "\n";
		$instruction .= sprintf(
			/* translators: 1: site URL, 2: admin URL */
			__( 'The WordPress site URL is %1$s and the URL to the admin interface is %2$s.', 'ai-services' ),
			$this->site_env->url( '/' ),
			$this->site_env->admin_url( '/' )
		) . "\n";
		$instruction .= __( 'You may also provide links to relevant sections of the WordPress admin interface, contextually for the site.', 'ai-services' ) . "\n";
		$instruction .= __( 'Only provide a link if it is relevant to the user’s question or request.', 'ai-services' ) . "\n";
		$instruction .= __( 'Any links provided must not be contained within the message text itself, but separately at the very end of the message.', 'ai-services' ) . "\n";
		$instruction .= sprintf(
			/* translators: %s: three hyphen characters */
			__( 'The link must be separated from the message text by three hyphens (%s).', 'ai-services' ),
			'---'
		) . "\n";
		$instruction .= __( 'After the link, you must provide a brief call-to-action text (no more than 4 words, no punctuation) that explains what the user can do with the link.', 'ai-services' ) . "\n";
		$instruction .= sprintf(
			/* translators: %s: three hyphen characters */
			__( 'This call-to-action test must be separated from the link by three hyphens (%s).', 'ai-services' ),
			'---'
		) . "\n";
		$instruction .= sprintf(
			/* translators: %s: example text */
			__( 'For example: %s', 'ai-services' ),
			'"' . __( 'You can edit posts in the Posts screen.', 'ai-services' ) . ' --- ' . $this->site_env->admin_url( 'edit.php' ) . ' --- ' . __( 'View posts', 'ai-services' ) . '"'
		) . "\n";
		$instruction .= __( 'Please provide the information in a clear and concise manner, and avoid using jargon or technical terms.', 'ai-services' ) . "\n";
		$instruction .= __( 'Do not provide any code snippets or technical details, unless specifically requested by the user.', 'ai-services' ) . "\n";
		$instruction .= __( 'Do not hallucinate or provide false information.', 'ai-services' ) . "\n";
		$instruction .= __( 'Here is some additional information about the WordPress site, so that you can help users more effectively:', 'ai-services' ) . "\n";
		$instruction .= __( 'You are here to help users with their questions and provide information.', 'ai-services' ) . "\n";
		$instruction .= $this->get_system_details();

		return $instruction;
	}

	/**
	 * Gets the system details for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The system details.
	 */
	private function get_system_details(): string {
		$details = '- ' . sprintf(
			/* translators: %s: WordPress version */
			__( 'The site is running on WordPress version %s.', 'ai-services' ),
			$this->site_env->info( 'version' )
		) . "\n";
		$details .= '- ' . sprintf(
			/* translators: %s: language code */
			__( 'The primary language of the site is %s.', 'ai-services' ),
			$this->site_env->info( 'language' )
		) . "\n";

		$themes = $this->site_env->get_active_themes();
		if ( count( $themes ) === 2 ) {
			$details .= '- ' . sprintf(
				/* translators: 1: parent theme, 2: child theme */
				__( 'The site is using the %1$s theme, with the %2$s child theme.', 'ai-services' ),
				$themes[1],
				$themes[0]
			) . "\n";
		} else {
			$details .= '- ' . sprintf(
				/* translators: %s theme */
				__( 'The site is using the %s theme.', 'ai-services' ),
				$themes[0]
			) . "\n";
		}

		if ( wp_is_block_theme() ) {
			$details .= '- ' . __( 'The theme is a block theme.', 'ai-services' ) . "\n";
		} else {
			$details .= '- ' . __( 'The theme is a classic theme.', 'ai-services' ) . "\n";
		}

		$active_plugins_info = $this->get_active_plugins_info();
		if ( count( $active_plugins_info ) > 0 ) {
			$details .= '- ' . __( 'The following plugins are active on the site:', 'ai-services' ) . "\n";
			$details .= '  - ' . implode( "\n  - ", $active_plugins_info ) . "\n";
		} else {
			$details .= '- ' . __( 'No plugins are active on the site.', 'ai-services' ) . "\n";
		}

		$current_user = $this->current_user->get();
		if ( $current_user->exists() ) {
			$wp_roles = wp_roles();
			if ( isset( $current_user->roles[0] ) && isset( $wp_roles->role_names[ $current_user->roles[0] ] ) ) {
				$role_name = translate_user_role( $wp_roles->role_names[ $current_user->roles[0] ] );
				$details  .= '- ' . sprintf(
					/* translators: %s theme */
					__( 'The current user has the role %s.', 'ai-services' ),
					$role_name
				) . "\n";
			}
		} else {
			$details .= '- ' . __( 'The current user is not logged in.', 'ai-services' ) . "\n";
		}

		if ( $this->network_env->is_multisite() ) {
			if ( is_main_site() ) {
				$details .= '- ' . __( 'The site is part of a multisite network. It is the main site of the network.', 'ai-services' ) . "\n";
			} else {
				$details .= '- ' . __( 'The site is part of a multisite network. It is a subsite of the network.', 'ai-services' ) . "\n";
			}
			if ( $this->current_user->is_super_admin() ) {
				$details .= '- ' . __( 'The current user is a network administrator.', 'ai-services' ) . "\n";
				$details .= '- ' . sprintf(
					/* translators: %s network admin URL */
					__( 'The URL to the network admin interface is %s.', 'ai-services' ),
					$this->network_env->admin_url( '/' )
				) . "\n";
			}
		}

		return $details;
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
						/* translators: %s: plugin slug */
						__( 'Unknown plugin %s', 'ai-services' ),
						trim( dirname( $plugin ), '/' )
					);
				}

				if ( isset( $plugins[ $plugin ]['Version'] ) && isset( $plugins[ $plugin ]['slug'] ) ) {
					return sprintf(
						/* translators: 1: plugin name, 2: plugin version, 3: plugin URL */
						_x( '%1$s (version %2$s), see %3$s', 'plugin info', 'ai-services' ),
						$plugins[ $plugin ]['Name'],
						$plugins[ $plugin ]['Version'],
						__( 'https://wordpress.org/plugins/', 'default' ) . $plugins[ $plugin ]['slug'] . '/'
					);
				}

				if ( isset( $plugins[ $plugin ]['Version'] ) ) {
					return sprintf(
						/* translators: 1: plugin name, 2: plugin version */
						_x( '%1$s (version %2$s)', 'plugin info', 'ai-services' ),
						$plugins[ $plugin ]['Name'],
						$plugins[ $plugin ]['Version']
					);
				}

				if ( isset( $plugins[ $plugin ]['slug'] ) ) {
					return sprintf(
						/* translators: 1: plugin name, 2: plugin URL */
						_x( '%1$s, see %2$s', 'plugin info', 'ai-services' ),
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
