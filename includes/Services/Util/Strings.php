<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\Strings
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

/**
 * Class providing static methods for string operations.
 *
 * @since n.e.x.t
 */
final class Strings {

	/**
	 * Converts a snake_case string to a camelCase string.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $input The snake_case string.
	 * @return string The camelCase string.
	 */
	public static function snake_case_to_camel_case( string $input ): string {
		return lcfirst( str_replace( '_', '', ucwords( $input, '_' ) ) );
	}
}
