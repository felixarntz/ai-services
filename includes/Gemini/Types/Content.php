<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Content
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types;

use InvalidArgumentException;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Class representing an entry of content for a generative model.
 *
 * @since n.e.x.t
 */
class Content implements Arrayable {

	/**
	 * The role of the content.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $role;

	/**
	 * The parts of the content.
	 *
	 * @since n.e.x.t
	 * @var Parts
	 */
	private $parts;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $role  The role of the content.
	 * @param Parts  $parts The parts of the content.
	 */
	public function __construct( string $role, Parts $parts ) {
		$this->role  = $role;
		$this->parts = $parts;
	}

	/**
	 * Gets the role of the content.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The role of the content.
	 */
	public function get_role(): string {
		return $this->role;
	}

	/**
	 * Gets the parts of the content.
	 *
	 * @since n.e.x.t
	 *
	 * @return Parts The parts of the content.
	 */
	public function get_parts(): Parts {
		return $this->parts;
	}

	/**
	 * Returns the array representation.
	 *
	 * @since n.e.x.t
	 *
	 * @return mixed[] Array representation.
	 */
	public function to_array(): array {
		return array(
			'role'  => $this->role,
			'parts' => $this->parts->to_array(),
		);
	}

	/**
	 * Creates a Content instance from an array of content data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array $data The content data.
	 * @return Content Content instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Content {
		if ( ! isset( $data['role'], $data['parts'] ) ) {
			throw new InvalidArgumentException( 'Content data must contain role and parts.' );
		}

		return new Content( $data['role'], Parts::from_array( $data['parts'] ) );
	}
}
