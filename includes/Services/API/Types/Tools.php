<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Tools
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use ArrayIterator;
use Felix_Arntz\AI_Services\Services\API\Types\Contracts\Tool;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Function_Declarations_Tool;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Web_Search_Tool;
use Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Collection;
use InvalidArgumentException;
use Traversable;

/**
 * Class representing a collection of content tools for a generative model.
 *
 * @since 0.5.0
 */
final class Tools implements Collection, Arrayable, With_JSON_Schema {

	/**
	 * The tools.
	 *
	 * @since 0.5.0
	 * @var Tool[]
	 */
	private $tools = array();

	/**
	 * Adds a function declarations tool.
	 *
	 * @since 0.5.0
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
	 * Adds a web search tool.
	 *
	 * @since n.e.x.t
	 *
	 * @param string[] $allowed_domains    Optional. The allowed domains. Default empty array.
	 * @param string[] $disallowed_domains Optional. The disallowed domains. Default empty array.
	 */
	public function add_web_search_tool( array $allowed_domains = array(), array $disallowed_domains = array() ): void {
		$this->add_tool(
			Web_Search_Tool::from_array(
				array(
					'webSearch' => array(
						'allowedDomains'    => $allowed_domains,
						'disallowedDomains' => $disallowed_domains,
					),
				)
			)
		);
	}

	/**
	 * Adds a tool.
	 *
	 * @since 0.5.0
	 *
	 * @param Tool $tool The tool.
	 */
	public function add_tool( Tool $tool ): void {
		$this->tools[] = $tool;
	}

	/**
	 * Returns an iterator for the tools collection.
	 *
	 * @since 0.5.0
	 *
	 * @return ArrayIterator<int, Tool> Collection iterator.
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->tools );
	}

	/**
	 * Returns the size of the tools collection.
	 *
	 * @since 0.5.0
	 *
	 * @return int Collection size.
	 */
	public function count(): int {
		return count( $this->tools );
	}

	/**
	 * Returns the tool at the given index.
	 *
	 * @since 0.5.0
	 *
	 * @param int $index The index.
	 * @return Tool The tool.
	 *
	 * @throws InvalidArgumentException Thrown if the index is out of bounds.
	 */
	public function get( int $index ): Tool {
		if ( ! isset( $this->tools[ $index ] ) ) {
			throw new InvalidArgumentException(
				'Index out of bounds.'
			);
		}
		return $this->tools[ $index ];
	}

	/**
	 * Returns the array representation.
	 *
	 * @since 0.5.0
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
	 * @since 0.5.0
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
			} elseif ( isset( $tool['webSearch'] ) ) {
				$tools->add_tool( Web_Search_Tool::from_array( $tool ) );
			} else {
				throw new InvalidArgumentException( 'Invalid tool data.' );
			}
		}

		return $tools;
	}

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		$function_declarations_tool_schema = Function_Declarations_Tool::get_json_schema();
		unset( $function_declarations_tool_schema['type'] );

		$web_search_tool_schema = Web_Search_Tool::get_json_schema();
		unset( $web_search_tool_schema['type'] );

		return array(
			'type'     => 'array',
			'minItems' => 1,
			'items'    => array(
				'type'  => 'object',
				'oneOf' => array(
					$function_declarations_tool_schema,
					$web_search_tool_schema,
				),
			),
		);
	}
}
