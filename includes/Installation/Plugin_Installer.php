<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Installation\Plugin_Installer
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Installation;

use Exception;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Installation\Abstract_Installer;

/**
 * Plugin installer (and uninstaller).
 *
 * @since n.e.x.t
 */
class Plugin_Installer extends Abstract_Installer {

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
		global $wpdb;

		// Delete all options with the plugin prefix.
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				// TODO: Ensure that this prefix is correct.
				'wpsp\_%'
			)
		);
	}
}
