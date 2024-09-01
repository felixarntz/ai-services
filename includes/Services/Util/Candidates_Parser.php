<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Util\Candidates_Parser
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Util;

use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidate;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidates;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Parts\Text_Part;

/**
 * Class providing static methods for working with candidate responses.
 *
 * @since n.e.x.t
 */
final class Candidates_Parser {

	/**
	 * Gets the text content from the first suitable candidate in the given list of candidates.
	 *
	 * @since n.e.x.t
	 *
	 * @param Candidates $candidates The candidates.
	 * @return string The text content. May contain line breaks.
	 */
	public static function get_candidates_text( Candidates $candidates ): string {
		$candidates = $candidates->filter( array( 'part_class_name' => Text_Part::class ) );
		$parts      = $candidates->get( 0 )->get_content()->get_parts();
		$text_parts = array();
		foreach ( $parts as $part ) {
			$text_parts[] = trim( $part->to_array()['text'] );
		}
		return implode( "\n\n", $text_parts );
	}

	/**
	 * Gets the text content from the given candidate.
	 *
	 * @since n.e.x.t
	 *
	 * @param Candidate $candidate The candidate.
	 * @return string The text content. May contain line breaks.
	 */
	public static function get_candidate_text( Candidate $candidate ): string {
		$parts      = $candidate->get_content()->get_parts()->filter( array( 'class_name' => Text_Part::class ) );
		$text_parts = array();
		foreach ( $parts as $part ) {
			$text_parts[] = trim( $part->to_array()['text'] );
		}
		return implode( "\n\n", $text_parts );
	}
}
