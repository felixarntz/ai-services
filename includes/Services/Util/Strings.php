<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\Strings
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

/**
 * Class providing static methods for string operations.
 *
 * @since 0.2.0
 */
final class Strings {

	/**
	 * Converts a snake_case string to a camelCase string.
	 *
	 * @since 0.2.0
	 *
	 * @param string $input The snake_case string.
	 * @return string The camelCase string.
	 */
	public static function snake_case_to_camel_case( string $input ): string {
		return lcfirst( str_replace( '_', '', ucwords( $input, '_' ) ) );
	}
}
