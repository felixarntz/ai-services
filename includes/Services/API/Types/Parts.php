<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Parts
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use ArrayIterator;
use Felix_Arntz\AI_Services\Services\API\Types\Contracts\Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Call_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Response_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Collection;
use InvalidArgumentException;
use Traversable;

/**
 * Class representing a collection of content parts for a generative model.
 *
 * @since 0.1.0
 */
final class Parts implements Collection, Arrayable, With_JSON_Schema {

	/**
	 * The parts of the content.
	 *
	 * @since 0.1.0
	 * @var Part[]
	 */
	private $parts = array();

	/**
	 * Adds a text part to the content.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
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
	 * @since 0.1.0
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
	 * Adds a function call part to the content.
	 *
	 * Every function call must have at least one of $id and $name provided.
	 *
	 * @since 0.5.0
	 *
	 * @param string               $id   The ID of the function call, or an empty string.
	 * @param string               $name The name of the function, or an empty string.
	 * @param array<string, mixed> $args The arguments of the function call.
	 */
	public function add_function_call_part( string $id, string $name, array $args ): void {
		$data = array();
		if ( $id ) {
			$data['id'] = $id;
		}
		if ( $name ) {
			$data['name'] = $name;
		}
		$data['args'] = $args;

		$this->add_part(
			Function_Call_Part::from_array(
				array( 'functionCall' => $data )
			)
		);
	}

	/**
	 * Adds a function response part to the content.
	 *
	 * Every function response must have at least one of $id and $name provided.
	 *
	 * @since 0.5.0
	 *
	 * @param string $id       The ID of the function response, or an empty string. If present, this must match the
	 *                         function call ID.
	 * @param string $name     The name of the function, or an empty string. If present, this must match the name of
	 *                         the function called.
	 * @param mixed  $response The function output response.
	 */
	public function add_function_response_part( string $id, string $name, $response ): void {
		$data = array();
		if ( $id ) {
			$data['id'] = $id;
		}
		if ( $name ) {
			$data['name'] = $name;
		}
		$data['response'] = $response;

		$this->add_part(
			Function_Response_Part::from_array(
				array( 'functionResponse' => $data )
			)
		);
	}

	/**
	 * Adds a part to the content.
	 *
	 * @since 0.1.0
	 *
	 * @param Part $part The part.
	 */
	public function add_part( Part $part ): void {
		$this->parts[] = $part;
	}

	/**
	 * Returns an iterator for the parts collection.
	 *
	 * @since 0.1.0
	 *
	 * @return ArrayIterator<int, Part> Collection iterator.
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->parts );
	}

	/**
	 * Returns the size of the parts collection.
	 *
	 * @since 0.1.0
	 *
	 * @return int Collection size.
	 */
	public function count(): int {
		return count( $this->parts );
	}

	/**
	 * Filters the parts collection by the given criteria.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
	 *
	 * @param int $index The index.
	 * @return Part The part.
	 *
	 * @throws InvalidArgumentException Thrown if the index is out of bounds.
	 */
	public function get( int $index ): Part {
		if ( ! isset( $this->parts[ $index ] ) ) {
			throw new InvalidArgumentException(
				'Index out of bounds.'
			);
		}
		return $this->parts[ $index ];
	}

	/**
	 * Returns the array representation.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
	 *
	 * @param mixed[] $data The parts data.
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
			} elseif ( isset( $part['functionCall'] ) ) {
				$parts->add_part( Function_Call_Part::from_array( $part ) );
			} elseif ( isset( $part['functionResponse'] ) ) {
				$parts->add_part( Function_Response_Part::from_array( $part ) );
			} else {
				throw new InvalidArgumentException( 'Invalid part data.' );
			}
		}

		return $parts;
	}

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		$text_part_schema              = Text_Part::get_json_schema();
		$inline_data_part_schema       = Inline_Data_Part::get_json_schema();
		$file_data_part_schema         = File_Data_Part::get_json_schema();
		$function_call_part_schema     = Function_Call_Part::get_json_schema();
		$function_response_part_schema = Function_Response_Part::get_json_schema();
		unset(
			$text_part_schema['type'],
			$inline_data_part_schema['type'],
			$file_data_part_schema['type'],
			$function_call_part_schema['type'],
			$function_response_part_schema['type']
		);

		return array(
			'type'     => 'array',
			'minItems' => 1,
			'items'    => array(
				'type'  => 'object',
				'oneOf' => array(
					$text_part_schema,
					$inline_data_part_schema,
					$file_data_part_schema,
					$function_call_part_schema,
					$function_response_part_schema,
				),
			),
		);
	}
}
