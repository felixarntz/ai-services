<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Util\Formatter
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Util;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use InvalidArgumentException;

/**
 * Class providing static methods for formatting content.
 *
 * @since 0.1.0
 */
final class Formatter {

	/**
	 * Formats and validates the various supported formats of a user prompt into a consistent list of Content instances.
	 *
	 * This method takes into account whether the provided content is supported by the given model, based on its capabilities.
	 *
	 * @since 0.5.0
	 *
	 * @param string|Parts|Content|Content[] $content      The content to format.
	 * @param string[]                       $capabilities The AI capabilities that the model supports.
	 * @return Content[] The formatted Content instances.
	 *
	 * @throws InvalidArgumentException Thrown if the content is invalid or the model does not support it.
	 */
	public static function format_and_validate_new_contents( $content, array $capabilities ): array {
		if ( is_array( $content ) ) {
			$contents = array_map(
				array( __CLASS__, 'format_new_content' ),
				$content
			);
		} else {
			$contents = array( self::format_new_content( $content ) );
		}

		if ( count( $contents ) === 0 ) {
			throw new InvalidArgumentException(
				esc_html__( 'No prompt was provided.', 'ai-services' )
			);
		}

		if ( Content_Role::USER !== $contents[0]->get_role() ) {
			throw new InvalidArgumentException(
				esc_html__( 'The first Content instance in the conversation or prompt must be user content.', 'ai-services' )
			);
		}

		if ( ! in_array( AI_Capability::CHAT_HISTORY, $capabilities, true ) && count( $contents ) > 1 ) {
			throw new InvalidArgumentException(
				esc_html__( 'The model does not support chat history. Only one content prompt must be provided.', 'ai-services' )
			);
		}

		if ( ! in_array( AI_Capability::MULTIMODAL_INPUT, $capabilities, true ) ) {
			// For performance reasons, only check the last content prompt, which likely is the only new one.
			$last_content         = $contents[ count( $contents ) - 1 ];
			$last_parts           = $last_content->get_parts();
			$last_parts_text_only = $last_parts->filter( array( 'class_name' => Text_Part::class ) );
			if ( count( $last_parts_text_only ) < count( $last_parts ) ) {
				throw new InvalidArgumentException(
					esc_html__( 'The model does not support multimodal input. Only text parts must be provided.', 'ai-services' )
				);
			}
		}

		return $contents;
	}

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
