<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Types\Safety_Setting
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google\Types;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

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
	 *
	 * @throws InvalidArgumentException Thrown if the given category or threshold is invalid.
	 */
	public function __construct( string $category, string $threshold ) {
		if ( ! $this->is_valid_category( $category ) ) {
			throw new InvalidArgumentException(
				esc_html(
					sprintf(
						/* translators: %s: invalid category encountered */
						__( 'The category %s is invalid.', 'ai-services' ),
						$category
					)
				)
			);
		}
		if ( ! $this->is_valid_threshold( $threshold ) ) {
			throw new InvalidArgumentException(
				esc_html(
					sprintf(
						/* translators: %s: invalid threshold encountered */
						__( 'The threshold %s is invalid.', 'ai-services' ),
						$threshold
					)
				)
			);
		}

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

	/**
	 * Creates a Safety_Setting instance from an array of content data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Safety_Setting Safety_Setting instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Safety_Setting {
		if ( ! isset( $data['category'], $data['threshold'] ) ) {
			throw new InvalidArgumentException( 'Safety_Setting data must contain category and threshold.' );
		}

		return new Safety_Setting( $data['category'], $data['threshold'] );
	}

	/**
	 * Checks if the given category is valid.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $category The category to check.
	 * @return bool True if the category is valid, false otherwise.
	 */
	private function is_valid_category( string $category ): bool {
		return in_array(
			$category,
			array(
				self::HARM_CATEGORY_HATE_SPEECH,
				self::HARM_CATEGORY_SEXUALLY_EXPLICIT,
				self::HARM_CATEGORY_HARASSMENT,
				self::HARM_CATEGORY_DANGEROUS_CONTENT,
			),
			true
		);
	}

	/**
	 * Checks if the given threshold is valid.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $threshold The threshold to check.
	 * @return bool True if the threshold is valid, false otherwise.
	 */
	private function is_valid_threshold( string $threshold ): bool {
		return in_array(
			$threshold,
			array(
				self::BLOCK_LOW_AND_ABOVE,
				self::BLOCK_MEDIUM_AND_ABOVE,
				self::BLOCK_ONLY_HIGH,
				self::BLOCK_NONE,
			),
			true
		);
	}
}
