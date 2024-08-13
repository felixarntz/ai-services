<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Installation\Plugin_Installer
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Installation;

use Exception;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Installation\Abstract_Installer;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option;

/**
 * Plugin installer (and uninstaller).
 *
 * @since n.e.x.t
 */
class Plugin_Installer extends Abstract_Installer {

	/**
	 * Option containing the main plugin data.
	 *
	 * @since n.e.x.t
	 * @var Option
	 */
	protected $plugin_data_option;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env $plugin_env         The plugin environment.
	 * @param Option     $version_option     Option to capture the installed version.
	 * @param Option     $delete_data_option Option to capture whether to delete data on uninstall.
	 * @param Option     $plugin_data_option Option containing the main plugin data.
	 */
	public function __construct(
		Plugin_Env $plugin_env,
		Option $version_option,
		Option $delete_data_option,
		Option $plugin_data_option
	) {
		parent::__construct( $plugin_env, $version_option, $delete_data_option );

		$this->plugin_data_option = $plugin_data_option;
	}

	/**
	 * Installs the full data for the plugin.
	 *
	 * @since n.e.x.t
	 *
	 * @throws Exception Thrown when installing fails.
	 */
	protected function install_data(): void {
		// Nothing to install.
	}

	/**
	 * Upgrades data for the plugin based on an old version used.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $old_version Old version number that is currently installed on the site.
	 *
	 * @throws Exception Thrown when upgrading fails.
	 */
	protected function upgrade_data( string $old_version ): void {
		// No upgrade routines yet.
	}

	/**
	 * Uninstalls the full data for the plugin.
	 *
	 * If this method is called, the administrator has explicitly opted in to deleting all plugin data.
	 *
	 * @since n.e.x.t
	 *
	 * @throws Exception Thrown when uninstalling fails.
	 */
	protected function uninstall_data(): void {
		$this->plugin_data_option->delete_value();
	}
}
