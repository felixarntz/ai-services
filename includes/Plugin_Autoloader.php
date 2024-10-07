<?php
/**
 * Class Felix_Arntz\AI_Services\Plugin_Autoloader
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services;

use InvalidArgumentException;

/**
 * Plugin class autoloader.
 *
 * @since 0.1.0
 */
class Plugin_Autoloader {

	/**
	 * Plugin root namespace.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $root_ns;

	/**
	 * Plugin class map.
	 *
	 * @since 0.1.0
	 * @var array<string, string>
	 */
	private $class_map;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
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
				esc_html__( 'The provided class map file does not return a class map array.', 'ai-services' )
			);
		}
	}

	/**
	 * Attempts to autoload the given PHP class.
	 *
	 * This method should be registered using spl_autoload_register().
	 *
	 * @since 0.1.0
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
