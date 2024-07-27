<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Parts
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types;

use InvalidArgumentException;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Class representing parts of content for a generative model.
 *
 * @since n.e.x.t
 */
final class Parts implements Arrayable {

	/**
	 * The parts of the content.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>[]
	 */
	private $parts = array();

	/**
	 * Adds a text part to the content.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $text The text.
	 */
	public function add_text_part( string $text ): void {
		$this->parts[] = array(
			'text' => $text,
		);
	}

	/**
	 * Adds an inline data part to the content.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $mime_type   The MIME type of the data.
	 * @param string $base64_data The base64-encoded data.
	 */
	public function add_inline_data_part( string $mime_type, string $base64_data ): void {
		$this->parts[] = array(
			'inlineData' => array(
				'mimeType' => $mime_type,
				'data'     => $base64_data,
			),
		);
	}

	/**
	 * Returns the number of parts.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The number of parts.
	 */
	public function count(): int {
		return count( $this->parts );
	}

	/**
	 * Returns the part at the given index.
	 *
	 * @since n.e.x.t
	 *
	 * @param int $index The index.
	 * @return array<string, mixed> The part.
	 *
	 * @throws InvalidArgumentException Thrown if the index is out of bounds.
	 */
	public function get( int $index ): array {
		if ( ! isset( $this->parts[ $index ] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'Index out of bounds.', 'wp-oop-plugin-lib-example' )
			);
		}
		return $this->parts[ $index ];
	}

	/**
	 * Returns the array representation.
	 *
	 * @since n.e.x.t
	 *
	 * @return mixed[] Array representation.
	 */
	public function to_array(): array {
		return $this->parts;
	}

	/**
	 * Creates a Parts instance from an array of parts data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array $data The parts data.
	 * @return Parts The Parts instance.
	 *
	 * @throws InvalidArgumentException Thrown if the parts data is invalid.
	 */
	public static function from_array( array $data ): Parts {
		$parts = new Parts();

		foreach ( $data as $part ) {
			if ( ! is_array( $part ) ) {
				throw new InvalidArgumentException( 'Invalid parts data.' );
			}

			if ( isset( $part['text'] ) ) {
				$parts->add_text_part( $part['text'] );
			} elseif ( isset( $part['inlineData'] ) ) {
				$inline_data = $part['inlineData'];
				if ( ! is_array( $inline_data ) || ! isset( $inline_data['mimeType'], $inline_data['data'] ) ) {
					throw new InvalidArgumentException( 'Invalid inline data part.' );
				}

				$parts->add_inline_data_part( $inline_data['mimeType'], $inline_data['data'] );
			} else {
				throw new InvalidArgumentException( 'Invalid part data.' );
			}
		}

		return $parts;
	}
}
