<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Parts
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Felix_Arntz\AI_Services\Services\Types;

use ArrayIterator;
use InvalidArgumentException;
use Traversable;
use Felix_Arntz\AI_Services\Services\Types\Contracts\Part;
use Felix_Arntz\AI_Services\Services\Types\Parts\File_Data_Part;
use Felix_Arntz\AI_Services\Services\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Collection;

/**
 * Class representing a collection of content parts for a generative model.
 *
 * @since n.e.x.t
 */
final class Parts implements Collection, Arrayable {

	/**
	 * The parts of the content.
	 *
	 * @since n.e.x.t
	 * @var Part[]
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
		$this->add_part(
			Text_Part::from_array( array( 'text' => $text ) )
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
		$this->add_part(
			Inline_Data_Part::from_array(
				array(
					'inlineData' => array(
						'mimeType' => $mime_type,
						'data'     => $base64_data,
					),
				)
			)
		);
	}

	/**
	 * Adds a file data part to the content.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $mime_type The MIME type of the data.
	 * @param string $file_uri  The URI of the file.
	 */
	public function add_file_data_part( string $mime_type, string $file_uri ): void {
		$this->add_part(
			File_Data_Part::from_array(
				array(
					'fileData' => array(
						'mimeType' => $mime_type,
						'fileUri'  => $file_uri,
					),
				)
			)
		);
	}

	/**
	 * Adds a part to the content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Part $part The part.
	 */
	public function add_part( Part $part ): void {
		$this->parts[] = $part;
	}

	/**
	 * Returns an iterator for the parts collection.
	 *
	 * @since n.e.x.t
	 *
	 * @return ArrayIterator<int, Part> Collection iterator.
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->parts );
	}

	/**
	 * Returns the size of the parts collection.
	 *
	 * @since n.e.x.t
	 *
	 * @return int Collection size.
	 */
	public function count(): int {
		return count( $this->parts );
	}

	/**
	 * Filters the parts collection by the given criteria.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $args {
	 *     The filter arguments.
	 *
	 *     @type string $class_name The class name to only allow parts of that class.
	 * }
	 * @return Parts The filtered parts collection.
	 */
	public function filter( array $args ): self {
		if ( isset( $args['class_name'] ) ) {
			$class_name = $args['class_name'];
			$map        = static function ( Part $part ) use ( $class_name ) {
				if ( $part instanceof $class_name ) {
					return call_user_func( array( $class_name, 'from_array' ), $part->to_array() );
				}
				return null;
			};
		} else {
			$map = static function ( Part $part ) {
				return call_user_func( array( get_class( $part ), 'from_array' ), $part->to_array() );
			};
		}

		$parts = new Parts();
		foreach ( $this->parts as $part ) {
			$mapped_part = $map( $part );
			if ( $mapped_part ) {
				$parts->add_part( $mapped_part );
			}
		}
		return $parts;
	}

	/**
	 * Returns the part at the given index.
	 *
	 * @since n.e.x.t
	 *
	 * @param int $index The index.
	 * @return Part The part.
	 *
	 * @throws InvalidArgumentException Thrown if the index is out of bounds.
	 */
	public function get( int $index ): Part {
		if ( ! isset( $this->parts[ $index ] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'Index out of bounds.', 'ai-services' )
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
		return array_map(
			static function ( Part $part ) {
				return $part->to_array();
			},
			$this->parts
		);
	}

	/**
	 * Creates a Parts instance from an array of parts data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The parts data.
	 * @return Parts The Parts instance.
	 *
	 * @throws InvalidArgumentException Thrown if the parts data is invalid.
	 */
	public static function from_array( array $data ): Parts {
		$parts = new Parts();

		foreach ( $data as $part ) {
			if ( ! is_array( $part ) ) {
				throw new InvalidArgumentException( 'Invalid part data.' );
			}

			if ( isset( $part['text'] ) ) {
				$parts->add_part( Text_Part::from_array( $part ) );
			} elseif ( isset( $part['inlineData'] ) ) {
				$parts->add_part( Inline_Data_Part::from_array( $part ) );
			} elseif ( isset( $part['fileData'] ) ) {
				$parts->add_part( File_Data_Part::from_array( $part ) );
			} else {
				throw new InvalidArgumentException( 'Invalid part data.' );
			}
		}

		return $parts;
	}
}
