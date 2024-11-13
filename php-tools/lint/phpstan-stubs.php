<?php
/**
 * File with extra stubs needed for PHPStan.
 *
 * These are typically for polyfills that WordPress Core provides for older PHP versions.
 * See https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/compat.php.
 *
 * @package wp-starter-plugin
 */

if ( ! function_exists( 'str_starts_with' ) ) {
	function str_starts_with( $haystack, $needle ) {
		if ( '' === $needle ) {
			return true;
		}

		return 0 === strpos( $haystack, $needle );
	}
}
