<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Enums\Abstract_Enum
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Enums;

use Felix_Arntz\AI_Services\Services\API\Enums\Contracts\Enum;

/**
 * Base class for an enum.
 *
 * @since 0.2.0
 */
abstract class Abstract_Enum implements Enum {

	/**
	 * The value map, to store in memory which values are valid.
	 *
	 * @since 0.2.0
	 * @var array<string, array<string, bool>>
	 */
	private static $value_map = array();

	/**
	 * Checks if the given value is valid for the enum.
	 *
	 * @since 0.2.0
	 *
	 * @param string $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	final public static function is_valid_value( string $value ): bool {
		$value_map = self::get_value_map_for_class( static::class );
		return isset( $value_map[ $value ] );
	}

	/**
	 * Gets the list of valid values for the enum.
	 *
	 * @since 0.2.0
	 *
	 * @return string[] The list of valid values.
	 */
	final public static function get_values(): array {
		$value_map = self::get_value_map_for_class( static::class );
		return array_keys( $value_map );
	}

	/**
	 * Gets the value map for the given child class name.
	 *
	 * @since 0.7.0
	 *
	 * @param string $class_name The child class name.
	 * @return array<string, bool> The value map.
	 */
	private static function get_value_map_for_class( string $class_name ): array {
		if ( ! isset( self::$value_map[ $class_name ] ) ) {
			self::$value_map[ $class_name ] = array_fill_keys( call_user_func( array( $class_name, 'get_all_values' ) ), true );
		}
		return self::$value_map[ $class_name ];
	}

	/**
	 * Gets all values for the enum.
	 *
	 * @since 0.2.0
	 *
	 * @return string[] The list of all values.
	 */
	abstract protected static function get_all_values(): array;
}
