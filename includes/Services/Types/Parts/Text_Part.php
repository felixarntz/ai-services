<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Parts\Text_Part
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types\Parts;

use InvalidArgumentException;

/**
 * Class for a text part of content for a generative model.
 *
 * @since n.e.x.t
 */
final class Text_Part extends Abstract_Part {

	/**
	 * Formats the data for the part.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The part data.
	 * @return array<string, mixed> Formatted data.
	 *
	 * @throws InvalidArgumentException Thrown if the part data is invalid.
	 */
	protected function format_data( array $data ): array {
		if ( ! isset( $data['text'] ) || ! is_string( $data['text'] ) ) {
			throw new InvalidArgumentException( 'The text part data must contain a string text value.' );
		}

		return array(
			'text' => $data['text'],
		);
	}

	/**
	 * Gets the default data for the part.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Default data.
	 */
	protected function get_default_data(): array {
		return array(
			'text' => '',
		);
	}
}
