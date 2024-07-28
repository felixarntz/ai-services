<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Candidate
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types;

use InvalidArgumentException;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;

/**
 * Class representing a candidate for a content response from a generative AI model.
 *
 * @since n.e.x.t
 */
final class Candidate implements Arrayable {

	/**
	 * The content.
	 *
	 * @since n.e.x.t
	 * @var Content
	 */
	private $content;

	/**
	 * Additional data for the candidate, if any.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $additional_data;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content              $content         The content.
	 * @param array<string, mixed> $additional_data Additional data for the candidate, if any.
	 */
	public function __construct( Content $content, array $additional_data = array() ) {
		$this->content = $content;

		// Remove the content from the additional data, if present, to prevent conflicts.
		unset( $additional_data['content'] );
		$this->additional_data = $additional_data;
	}

	/**
	 * Gets the content.
	 *
	 * @since n.e.x.t
	 *
	 * @return Content The content.
	 */
	public function get_content(): Content {
		return $this->content;
	}

	/**
	 * Gets the additional data.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The additional data.
	 */
	public function get_additional_data(): array {
		return $this->additional_data;
	}

	/**
	 * Converts the candidate to an array.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The array representation of the candidate.
	 */
	public function to_array(): array {
		return array_merge(
			array(
				'content' => $this->content->to_array(),
			),
			$this->additional_data
		);
	}

	/**
	 * Creates a Candidate instance from an array of content data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Candidate Candidate instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Candidate {
		if ( ! isset( $data['content'] ) ) {
			throw new InvalidArgumentException( 'Candidate data must contain content.' );
		}

		/*
		 * Apparently, the API sometimes omits this.
		 * Given candidates are always part of a model response, we can safely assume the role is 'model'.
		 */
		if ( ! isset( $data['content']['role'] ) ) {
			$data['content']['role'] = 'model';
		}

		$content = Content::from_array( $data['content'] );
		unset( $data['content'] );

		return new Candidate( $content, $data );
	}
}
