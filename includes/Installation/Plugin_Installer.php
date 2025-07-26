<?php
/**
 * Class Felix_Arntz\AI_Services\Installation\Plugin_Installer
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Installation;

use Exception;
use Felix_Arntz\AI_Services\Services\Cache\Service_Request_Cache;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Installation\Abstract_Installer;

/**
 * Plugin installer (and uninstaller).
 *
 * @since 0.1.0
 */
class Plugin_Installer extends Abstract_Installer {

	/**
	 * Installs the full data for the plugin.
	 *
	 * @since 0.1.0
	 *
	 * @throws Exception Thrown when installing fails.
	 */
	protected function install_data(): void {
		// Nothing to install.
	}

	/**
	 * Upgrades data for the plugin based on an old version used.
	 *
	 * @since 0.1.0
	 *
	 * @param string $old_version Old version number that is currently installed on the site.
	 *
	 * @throws Exception Thrown when upgrading fails.
	 */
	protected function upgrade_data( string $old_version ): void {
		if ( version_compare( $old_version, '0.7.0', '<' ) ) {
			// Invalidate all existing service caches since return shape of `list_models()` method changed.
			Service_Request_Cache::invalidate_caches( 'anthropic' );
			Service_Request_Cache::invalidate_caches( 'google' );
			Service_Request_Cache::invalidate_caches( 'openai' );
		}
	}

	/**
	 * Uninstalls the full data for the plugin.
	 *
	 * If this method is called, the administrator has explicitly opted in to deleting all plugin data.
	 *
	 * @since 0.1.0
	 *
	 * @throws Exception Thrown when uninstalling fails.
	 */
	protected function uninstall_data(): void {
		global $wpdb;

		/*
		 * Delete all options with the plugin prefix.
		 * This is okay as a direct database query since it only runs once when uninstalling.
		 */
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( 'ais_' ) . '%'
			)
		);

		/*
		 * Delete all (site-specific) user metadata with the plugin prefix.
		 * This is okay as a direct database query since it only runs once when uninstalling.
		 */
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( $wpdb->get_blog_prefix() . 'ais_' ) . '%'
			)
		);
	}
}
