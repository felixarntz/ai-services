<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Tools
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use ArrayIterator;
use Felix_Arntz\AI_Services\Services\API\Types\Contracts\Tool;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Function_Declarations_Tool;
use Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Collection;
use InvalidArgumentException;
use Traversable;

/**
 * Class representing a collection of content tools for a generative model.
 *
 * @since n.e.x.t
 */
final class Tools implements Collection, Arrayable, With_JSON_Schema {

	/**
	 * The tools.
	 *
	 * @since n.e.x.t
	 * @var Tool[]
	 */
	private $tools = array();

	/**
	 * Adds a function declarations tool.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed>[] $function_declarations The function declarations.
	 */
	public function add_function_declarations_tool( array $function_declarations ): void {
		$this->add_tool(
			Function_Declarations_Tool::from_array(
				array( 'functionDeclarations' => $function_declarations )
			)
		);
	}

	/**
	 * Adds a tool.
	 *
	 * @since n.e.x.t
	 *
	 * @param Tool $tool The tool.
	 */
	public function add_tool( Tool $tool ): void {
		$this->tools[] = $tool;
	}

	/**
	 * Returns an iterator for the tools collection.
	 *
	 * @since n.e.x.t
	 *
	 * @return ArrayIterator<int, Tool> Collection iterator.
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->tools );
	}

	/**
	 * Returns the size of the tools collection.
	 *
	 * @since n.e.x.t
	 *
	 * @return int Collection size.
	 */
	public function count(): int {
		return count( $this->tools );
	}

	/**
	 * Returns the tool at the given index.
	 *
	 * @since n.e.x.t
	 *
	 * @param int $index The index.
	 * @return Tool The tool.
	 *
	 * @throws InvalidArgumentException Thrown if the index is out of bounds.
	 */
	public function get( int $index ): Tool {
		if ( ! isset( $this->tools[ $index ] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'Index out of bounds.', 'ai-services' )
			);
		}
		return $this->tools[ $index ];
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
			static function ( Tool $tool ) {
				return $tool->to_array();
			},
			$this->tools
		);
	}

	/**
	 * Creates a Tools instance from an array of tools data.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed[] $data The tools data.
	 * @return Tools The Tools instance.
	 *
	 * @throws InvalidArgumentException Thrown if the tools data is invalid.
	 */
	public static function from_array( array $data ): Tools {
		$tools = new Tools();

		foreach ( $data as $tool ) {
			if ( ! is_array( $tool ) ) {
				throw new InvalidArgumentException( 'Invalid tool data.' );
			}

			if ( isset( $tool['functionDeclarations'] ) ) {
				$tools->add_tool( Function_Declarations_Tool::from_array( $tool ) );
			} else {
				throw new InvalidArgumentException( 'Invalid tool data.' );
			}
		}

		return $tools;
	}

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		$function_declarations_tool_schema = Function_Declarations_Tool::get_json_schema();
		unset( $function_declarations_tool_schema['type'] );

		return array(
			'type'     => 'array',
			'minItems' => 1,
			'items'    => array(
				'type'  => 'object',
				'oneOf' => array(
					$function_declarations_tool_schema,
				),
			),
		);
	}
}
