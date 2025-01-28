<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Tools\Abstract_Tool
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types\Tools;

use Felix_Arntz\AI_Services\Services\API\Types\Contracts\Tool;
use Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema;
use InvalidArgumentException;

/**
 * Base class for a tool for a generative model.
 *
 * @since n.e.x.t
 */
abstract class Abstract_Tool implements Tool, With_JSON_Schema {

	/**
	 * The tool data.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $data = array();

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 */
	final public function __construct() {
		// Empty constructor, only to prevent override.
	}

	/**
	 * Sets data for the tool.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The tool data.
	 */
	final public function set_data( array $data ): void {
		$this->data = $this->format_data( $data );
	}

	/**
	 * Formats the data for the tool.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The tool data.
	 * @return array<string, mixed> Formatted data.
	 *
	 * @throws InvalidArgumentException Thrown if the tool data is invalid.
	 */
	abstract protected function format_data( array $data ): array;

	/**
	 * Gets the default data for the tool.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Default data.
	 */
	abstract protected function get_default_data(): array;

	/**
	 * Returns the array representation.
	 *
	 * @since n.e.x.t
	 *
	 * @return mixed[] Array representation.
	 */
	final public function to_array(): array {
		if ( ! $this->data ) {
			$this->data = $this->get_default_data();
		}
		return $this->data;
	}

	/**
	 * Creates a specific Tool instance from an array of tool data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The tool data.
	 * @return Tool The Tool instance.
	 *
	 * @throws InvalidArgumentException Thrown if the tools data is invalid.
	 */
	final public static function from_array( array $data ): Tool {
		$tool = new static();
		$tool->set_data( $data );
		return $tool;
	}
}
