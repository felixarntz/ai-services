<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Util\Formatter
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Util;

use InvalidArgumentException;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Content;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Parts;

/**
 * Class providing static methods for formatting content.
 *
 * @since n.e.x.t
 */
class Formatter {

	/**
	 * Formats the various supported formats of new user content into a consistent Content instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content $content The content to format.
	 * @return Content The formatted new content.
	 */
	public static function format_new_content( $content ): Content {
		return self::format_content( $content, 'user' );
	}

	/**
	 * Formats the various supported formats of a system instruction into a consistent Content instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content $input The system instruction to format.
	 * @return Content The formatted system instruction.
	 */
	public static function format_system_instruction( $input ): Content {
		return self::format_content( $input, 'system' );
	}

	/**
	 * Formats the various supported formats of content into a consistent Content instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content $input The content to format.
	 * @param string               $role  The role for the content.
	 * @return Content The formatted content.
	 *
	 * @throws InvalidArgumentException Thrown if the value is not a string, a Parts instance, or a Content instance.
	 */
	public static function format_content( $input, string $role ): Content {
		if ( is_string( $input ) ) {
			$parts = new Parts();
			$parts->add_text_part( $input );

			return new Content( $role, $parts );
		}

		if ( $input instanceof Parts ) {
			return new Content( $role, $input );
		}

		if ( ! $input instanceof Content ) {
			throw new InvalidArgumentException(
				esc_html__( 'The value must be a string, a Parts instance, or a Content instance.', 'wp-oop-plugin-lib-example' )
			);
		}

		return $input;
	}
}
