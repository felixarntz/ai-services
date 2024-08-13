<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Plugin_Autoloader
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin;

use InvalidArgumentException;

/**
 * Plugin class autoloader.
 *
 * @since n.e.x.t
 */
class Plugin_Autoloader {

	/**
	 * Plugin root namespace.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $root_ns;

	/**
	 * Plugin class map.
	 *
	 * @since n.e.x.t
	 * @var array<string, string>
	 */
	private $class_map;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $root_ns        Plugin root namespace.
	 * @param string $class_map_file Absolute path to a PHP file that returns the plugin class map array.
	 *
	 * @throws InvalidArgumentException Thrown when an invalid class map file is provided.
	 */
	public function __construct( string $root_ns, string $class_map_file ) {
		$this->root_ns   = rtrim( $root_ns, '\\' ) . '\\';
		$this->class_map = require $class_map_file;

		if ( ! is_array( $this->class_map ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'The provided class map file does not return a class map array.', 'wp-starter-plugin' )
			);
		}
	}

	/**
	 * Attempts to autoload the given PHP class.
	 *
	 * This method should be registered using spl_autoload_register().
	 *
	 * @since n.e.x.t
	 *
	 * @param string $class_name PHP class name.
	 */
	public function autoload( string $class_name ): void {
		// Bail if class is not in the class map.
		if ( ! isset( $this->class_map[ $class_name ] ) ) {
			return;
		}

		// Bail if class is not part of the plugin.
		if ( ! str_starts_with( $class_name, $this->root_ns ) ) {
			return;
		}

		require_once $this->class_map[ $class_name ];
	}
}
