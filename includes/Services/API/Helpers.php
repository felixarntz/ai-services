<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Helpers
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Generator;
use WP_Post;

/**
 * Class providing static helper methods as part of the public API.
 *
 * @since 0.2.0
 */
final class Helpers {

	/**
	 * Converts a text string to a Content instance.
	 *
	 * @since 0.2.0
	 *
	 * @param string $text The text.
	 * @param string $role Optional. The role to use for the content. Default 'user'.
	 * @return Content The content instance.
	 */
	public static function text_to_content( string $text, string $role = Content_Role::USER ): Content {
		return Formatter::format_content( $text, $role );
	}

	/**
	 * Converts a text string and attachment to a multimodal Content instance.
	 *
	 * The text will be included as a prompt as the first part of the content, and the attachment (e.g. an image or
	 * audio file) will be included as the second part.
	 *
	 * @since n.e.x.t
	 *
	 * @param string      $text       The text.
	 * @param int|WP_Post $attachment The attachment ID or object.
	 * @param string      $role       Optional. The role to use for the content. Default 'user'.
	 * @return Content The content instance.
	 */
	public static function text_and_attachment_to_content( string $text, $attachment, string $role = Content_Role::USER ): Content {
		if ( $attachment instanceof WP_Post ) {
			$attachment_id = (int) $attachment->ID;
		} else {
			$attachment_id = (int) $attachment;
			$attachment    = get_post( $attachment_id );
		}

		$file       = get_attached_file( $attachment_id );
		$large_size = image_get_intermediate_size( $attachment_id, 'large' );
		if ( $large_size && isset( $large_size['path'] ) ) {
			// To get the absolute path to a sub-size file, we need to prepend the uploads dir.
			if ( str_starts_with( $large_size['path'], '/' ) ) {
				$file = $large_size['path'];
			} else {
				$uploads = wp_get_upload_dir();
				if ( false === $uploads['error'] ) {
					$file = "{$uploads['basedir']}/{$large_size['path']}";
				}
			}
		}

		$mime_type = wp_check_filetype( $file );
		if ( isset( $mime_type['type'] ) ) {
			$mime_type = $mime_type['type'];
		} else {
			// Fallback that should never be needed.
			$mime_type = $attachment->post_mime_type;
		}

		$parts = new Parts();
		$parts->add_text_part( $text );
		$parts->add_inline_data_part( $mime_type, self::base64_encode_file( $file, $mime_type ) );

		return Formatter::format_content( $parts, $role );
	}

	/**
	 * Converts a Content instance to a text string.
	 *
	 * This method will return the combined text from all consecutive text parts in the content.
	 * Realistically, this should almost always return the text from just one part, as API responses typically do not
	 * contain multiple text parts in a row - but it might be possible.
	 *
	 * @since 0.2.0
	 *
	 * @param Content $content The content instance.
	 * @return string The text, or an empty string if there are no text parts.
	 */
	public static function content_to_text( Content $content ): string {
		$parts = $content->get_parts();

		$text_parts = array();
		foreach ( $parts as $part ) {
			/*
			 * If there is any non-text part present, we want to ensure that no interrupted text content is returned.
			 * Therefore, we break the loop as soon as we encounter a non-text part, unless no text parts have been
			 * found yet, in which case the text may only start with a later part.
			 */
			if ( ! $part instanceof Text_Part ) {
				if ( count( $text_parts ) > 0 ) {
					break;
				}
				continue;
			}

			$text_parts[] = $part->get_text();
		}

		if ( count( $text_parts ) === 0 ) {
			return '';
		}

		return implode( "\n\n", $text_parts );
	}

	/**
	 * Gets the text from the first Content instance in the given list which contains text.
	 *
	 * @since 0.2.0
	 *
	 * @param Content[] $contents The list of Content instances.
	 * @return string The text, or an empty string if no Content instance has text parts.
	 */
	public static function get_text_from_contents( array $contents ): string {
		foreach ( $contents as $content ) {
			$text = self::content_to_text( $content );
			if ( '' !== $text ) {
				return $text;
			}
		}

		return '';
	}

	/**
	 * Gets the first Content instance in the given list which contains text.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $contents The list of Content instances.
	 * @return Content|null The Content instance, or null if no Content instance has text parts.
	 */
	public static function get_text_content_from_contents( array $contents ): ?Content {
		foreach ( $contents as $content ) {
			$text = self::content_to_text( $content );
			if ( '' !== $text ) {
				return $content;
			}
		}

		return null;
	}

	/**
	 * Gets the Content instances for each candidate in the given list.
	 *
	 * @since 0.2.0
	 *
	 * @param Candidates $candidates The list of candidates.
	 * @return Content[] The list of Content instances.
	 */
	public static function get_candidate_contents( Candidates $candidates ): array {
		$contents = array();

		foreach ( $candidates as $candidate ) {
			$content = $candidate->get_content();
			if ( ! $content ) {
				continue;
			}
			$contents[] = $content;
		}

		return $contents;
	}

	/**
	 * Processes a stream of candidates, aggregating the candidates chunks into a single candidates instance.
	 *
	 * This method returns a stream processor instance that can be used to read all chunks from the given candidates
	 * generator and process them with a callback. Alternatively, you can read from the generator yourself and provide
	 * all chunks to the processor manually.
	 *
	 * @since 0.3.0
	 *
	 * @param Generator<Candidates> $generator The generator that yields the chunks of response candidates.
	 * @return Candidates_Stream_Processor The stream processor instance.
	 */
	public static function process_candidates_stream( Generator $generator ): Candidates_Stream_Processor { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
		return new Candidates_Stream_Processor( $generator );
	}

	/**
	 * Base64-encodes a file and returns its data URL.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $file      Absolute path to the file, or its URL.
	 * @param string $mime_type Optional. The MIME type of the file. If provided, the base64-encoded data URL will
	 *                          be prefixed with `data:{mime_type};base64,`. Default empty string.
	 * @return string The base64-encoded file data URL, or empty string on failure.
	 */
	public static function base64_encode_file( string $file, string $mime_type = '' ): string {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$binary_data = file_get_contents( $file );
		if ( ! $binary_data ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$base64 = base64_encode( $binary_data );
		if ( '' !== $mime_type ) {
			$base64 = "data:$mime_type;base64,$base64";
		}
		return $base64;
	}
}
