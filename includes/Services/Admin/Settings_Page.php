<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Admin\Settings_Page
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib-example
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Admin;

use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Abstract_Admin_Page;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;

/**
 * Class representing the plugin's admin settings page.
 *
 * @since n.e.x.t
 */
class Settings_Page extends Abstract_Admin_Page {

	/**
	 * The plugin environment.
	 *
	 * @since n.e.x.t
	 * @var Plugin_Env
	 */
	private $plugin_env;

	/**
	 * WordPress script registry.
	 *
	 * @since n.e.x.t
	 * @var Script_Registry
	 */
	private $script_registry;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env      $plugin_env      The plugin environment.
	 * @param Script_Registry $script_registry WordPress script registry.
	 */
	public function __construct( Plugin_Env $plugin_env, Script_Registry $script_registry ) {
		parent::__construct();

		$this->plugin_env      = $plugin_env;
		$this->script_registry = $script_registry;
	}

	/**
	 * Initializes functionality for the admin page.
	 *
	 * @since n.e.x.t
	 */
	public function load(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				$this->script_registry->register(
					'wpoopple_services',
					array(
						'src'      => $this->plugin_env->url( 'build/index.js' ),
						'manifest' => $this->plugin_env->path( 'build/index.asset.php' ),
						'strategy' => 'defer',
					)
				);
				$this->script_registry->enqueue( 'wpoopple_services' );
			}
		);
	}

	/**
	 * Renders the admin page.
	 *
	 * @since n.e.x.t
	 */
	public function render(): void {
		// TODO.
		echo '<div id="settings-page-root" class="wrap">Settings page test content.</div>';
	}

	/**
	 * Returns the admin page slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page slug.
	 */
	protected function slug(): string {
		return 'wpoopple_services';
	}

	/**
	 * Returns the admin page title.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page title.
	 */
	protected function title(): string {
		return __( 'AI Services', 'wp-oop-plugin-lib-example' );
	}

	/**
	 * Returns the admin page's required capability.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page capability.
	 */
	protected function capability(): string {
		return 'wpoopple_manage_services';
	}
}
