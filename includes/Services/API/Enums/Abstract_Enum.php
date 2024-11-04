<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Enums\Abstract_Enum
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Enums;

use Felix_Arntz\AI_Services\Services\API\Enums\Contracts\Enum;

/**
 * Base class for an enum.
 *
 * @since n.e.x.t
 */
abstract class Abstract_Enum implements Enum {

	/**
	 * The value map, to store in memory which values are valid.
	 *
	 * @since n.e.x.t
	 * @var array<string, bool>|null
	 */
	private static $value_map = null;

	/**
	 * Checks if the given value is valid for the enum.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	final public static function is_valid_value( string $value ): bool {
		if ( null === self::$value_map ) {
			self::$value_map = array_fill_keys( static::get_all_values(), true );
		}
		return isset( self::$value_map[ $value ] );
	}

	/**
	 * Gets the list of valid values for the enum.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The list of valid values.
	 */
	final public static function get_values(): array {
		if ( null === self::$value_map ) {
			self::$value_map = array_fill_keys( static::get_all_values(), true );
		}
		return array_keys( self::$value_map );
	}

	/**
	 * Gets all values for the enum.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The list of all values.
	 */
	abstract protected static function get_all_values(): array;
}
