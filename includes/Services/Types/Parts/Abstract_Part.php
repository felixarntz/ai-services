<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Parts\Abstract_Part
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types\Parts;

use Felix_Arntz\AI_Services\Services\Types\Contracts\Part;
use InvalidArgumentException;

/**
 * Base class for a part of content for a generative model.
 *
 * @since 0.1.0
 */
abstract class Abstract_Part implements Part {

	/**
	 * The part data.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	private $data = array();

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	final public function __construct() {
		// Empty constructor, only to prevent override.
	}

	/**
	 * Sets data for the part.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $data The part data.
	 */
	final public function set_data( array $data ): void {
		$this->data = $this->format_data( $data );
	}

	/**
	 * Formats the data for the part.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Default data.
	 */
	abstract protected function get_default_data(): array;

	/**
	 * Returns the array representation.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
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
