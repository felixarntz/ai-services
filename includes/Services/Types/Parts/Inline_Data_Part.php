<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Parts\Inline_Data_Part
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types\Parts;

use InvalidArgumentException;

/**
 * Class for an inline data part of content for a generative model.
 *
 * @since n.e.x.t
 */
final class Inline_Data_Part extends Abstract_Part {

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
		if ( ! isset( $data['inlineData'] ) || ! is_array( $data['inlineData'] ) ) {
			throw new InvalidArgumentException( 'The inline data part data must contain an associative array inlineData value.' );
		}

		$inline_data = $data['inlineData'];

		if ( ! isset( $inline_data['mimeType'] ) || ! is_string( $inline_data['mimeType'] ) ) {
			throw new InvalidArgumentException( 'The inline data part data must contain a string mimeType value.' );
		}

		if ( ! isset( $inline_data['data'] ) || ! is_string( $inline_data['data'] ) ) {
			throw new InvalidArgumentException( 'The inline data part data must contain a string data value.' );
		}

		return array(
			'inlineData' => array(
				'mimeType' => $inline_data['mimeType'],
				'data'     => $inline_data['data'],
			),
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
			'inlineData' => array(
				'mimeType' => '',
				'data'     => '',
			),
		);
	}
}
