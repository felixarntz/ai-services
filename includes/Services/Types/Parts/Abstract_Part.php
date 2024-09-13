<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Parts\Abstract_Part
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Felix_Arntz\AI_Services\Services\Types\Parts;

use Felix_Arntz\AI_Services\Services\Types\Contracts\Part;
use InvalidArgumentException;

/**
 * Base class for a part of content for a generative model.
 *
 * @since n.e.x.t
 */
abstract class Abstract_Part implements Part {

	/**
	 * The part data.
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
	 * Sets data for the part.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The part data.
	 */
	final public function set_data( array $data ): void {
		$this->data = $this->format_data( $data );
	}

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
	abstract protected function format_data( array $data ): array;

	/**
	 * Gets the default data for the part.
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
	 * Creates a specific Part instance from an array of part data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The part data.
	 * @return Part The Part instance.
	 *
	 * @throws InvalidArgumentException Thrown if the parts data is invalid.
	 */
	final public static function from_array( array $data ): Part {
		$part = new static();
		$part->set_data( $data );
		return $part;
	}
}
