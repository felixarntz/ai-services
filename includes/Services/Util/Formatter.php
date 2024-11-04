<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\Formatter
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use InvalidArgumentException;

/**
 * Class providing static methods for formatting content.
 *
 * @since 0.1.0
 */
final class Formatter {

	/**
	 * Formats the various supported formats of new user content into a consistent Content instance.
	 *
	 * @since 0.1.0
	 *
	 * @param string|Parts|Content $content The content to format.
	 * @return Content The formatted new content.
	 */
	public static function format_new_content( $content ): Content {
		return self::format_content( $content, Content_Role::USER );
	}

	/**
	 * Formats the various supported formats of a system instruction into a consistent Content instance.
	 *
	 * @since 0.1.0
	 *
	 * @param string|Parts|Content $input The system instruction to format.
	 * @return Content The formatted system instruction.
	 */
	public static function format_system_instruction( $input ): Content {
		return self::format_content( $input, Content_Role::SYSTEM );
	}

	/**
	 * Formats the various supported formats of content into a consistent Content instance.
	 *
	 * @since 0.1.0
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
				esc_html__( 'The value must be a string, a Parts instance, or a Content instance.', 'ai-services' )
			);
		}

		return $input;
	}
}
