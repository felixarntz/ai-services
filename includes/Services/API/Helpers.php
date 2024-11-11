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
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Generator;

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
	 * @since n.e.x.t
	 *
	 * @param Generator<Candidates> $generator The generator that yields the chunks of response candidates.
	 * @return Candidates_Stream_Processor The stream processor instance.
	 */
	public static function process_candidates_stream( Generator $generator ): Candidates_Stream_Processor {
		return new Candidates_Stream_Processor( $generator );
	}
}
