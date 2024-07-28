<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Safety_Setting
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types;

use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Class representing a safety setting that can be sent as part of request parameters.
 *
 * @since n.e.x.t
 */
class Safety_Setting implements Arrayable {

	const HARM_CATEGORY_HATE_SPEECH       = 'HARM_CATEGORY_HATE_SPEECH';
	const HARM_CATEGORY_SEXUALLY_EXPLICIT = 'HARM_CATEGORY_SEXUALLY_EXPLICIT';
	const HARM_CATEGORY_HARASSMENT        = 'HARM_CATEGORY_HARASSMENT';
	const HARM_CATEGORY_DANGEROUS_CONTENT = 'HARM_CATEGORY_DANGEROUS_CONTENT';

	const BLOCK_LOW_AND_ABOVE    = 'BLOCK_LOW_AND_ABOVE';
	const BLOCK_MEDIUM_AND_ABOVE = 'BLOCK_MEDIUM_AND_ABOVE';
	const BLOCK_ONLY_HIGH        = 'BLOCK_ONLY_HIGH';
	const BLOCK_NONE             = 'BLOCK_NONE';

	/**
	 * The safety setting category.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $category;

	/**
	 * The safety setting threshold.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $threshold;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $category  The safety setting category.
	 * @param string $threshold The safety setting threshold.
	 */
	public function __construct( string $category, string $threshold ) {
		$this->category  = $category;
		$this->threshold = $threshold;
	}

	/**
	 * Get the safety setting category.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The safety setting category.
	 */
	public function get_category(): string {
		return $this->category;
	}

	/**
	 * Get the safety setting threshold.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The safety setting threshold.
	 */
	public function get_threshold(): string {
		return $this->threshold;
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
			'category'  => $this->category,
			'threshold' => $this->threshold,
		);
	}
}
