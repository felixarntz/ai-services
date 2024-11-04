<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Admin\Plugin_Action_Link
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Admin;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Admin_Menu;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Site_Env;

/**
 * Class for a plugin table action link pointing to the plugin's admin settings page.
 *
 * @since 0.2.0
 */
class Plugin_Action_Link {

	/**
	 * WordPress admin menu.
	 *
	 * @since 0.2.0
	 * @var Admin_Menu
	 */
	private $admin_menu;

	/**
	 * The plugin's settings page.
	 *
	 * @since 0.2.0
	 * @var Settings_Page
	 */
	private $settings_page;

	/**
	 * Site environment.
	 *
	 * @since 0.2.0
	 * @var Site_Env
	 */
	private $site_env;

	/**
	 * Constructor.
	 *
	 * @since 0.2.0
	 *
	 * @param Admin_Menu    $admin_menu    WordPress admin menu.
	 * @param Settings_Page $settings_page The plugin's settings page.
	 * @param Site_Env      $site_env      Site environment.
	 */
	public function __construct( Admin_Menu $admin_menu, Settings_Page $settings_page, Site_Env $site_env ) {
		$this->admin_menu    = $admin_menu;
		$this->settings_page = $settings_page;
		$this->site_env      = $site_env;
	}

	/**
	 * Gets the capability the current user needs to access the link.
	 *
	 * @since 0.2.0
	 *
	 * @return string The capability.
	 */
	public function get_capability(): string {
		return $this->settings_page->get_capability();
	}

	/**
	 * Gets the HTML for the action link.
	 *
	 * @since 0.2.0
	 *
	 * @return string The HTML for the action link.
	 */
	public function get_html(): string {
		$menu_slug = $this->admin_menu->get_slug();
		if ( str_ends_with( $menu_slug, '.php' ) ) {
			$menu_file = $menu_slug;
		} else {
			$menu_file = 'admin.php';
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(
				add_query_arg(
					'page',
					$this->settings_page->get_slug(),
					$this->site_env->admin_url( $menu_file )
				)
			),
			esc_html__( 'Settings', 'ai-services' )
		);
	}
}
