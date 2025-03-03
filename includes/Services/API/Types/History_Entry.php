<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\History_Entry
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use InvalidArgumentException;

/**
 * Class representing a single entry in a chat history.
 *
 * @since 0.5.0
 */
final class History_Entry {

	/**
	 * The history entry's content.
	 *
	 * @since 0.5.0
	 * @var Content
	 */
	private $content;

	/**
	 * Additional data for the history entry, if any.
	 *
	 * @since 0.5.0
	 * @var array<string, mixed>
	 */
	private $additional_data;

	/**
	 * Constructor.
	 *
	 * @since 0.5.0
	 *
	 * @param Content              $content         The history entry content.
	 * @param array<string, mixed> $additional_data Additional data for the history entry, if any.
	 */
	public function __construct( Content $content, array $additional_data = array() ) {
		$this->content = $content;

		// Remove the content from the additional data, if present, to prevent conflicts.
		unset( $additional_data['content'] );
		$this->additional_data = $additional_data;
	}

	/**
	 * Gets the history entry content.
	 *
	 * @since 0.5.0
	 *
	 * @return Content The content.
	 */
	public function get_content(): Content {
		return $this->content;
	}

	/**
	 * Gets the additional data.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> The additional data.
	 */
	public function get_additional_data(): array {
		return $this->additional_data;
	}

	/**
	 * Converts the candidate to an array.
	 *
	 * @since 0.5.0
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
	 * Creates a History_Entry instance from an array of content data.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return History_Entry History_Entry instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): History_Entry {
		if ( ! isset( $data['content'] ) ) {
			throw new InvalidArgumentException( 'History entry data must contain content.' );
		}

		$content = Content::from_array( $data['content'] );
		unset( $data['content'] );

		return new History_Entry( $content, $data );
	}

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'content' => array_merge(
					array(
						'description' => __( 'History entry content.', 'ai-services' ),
						'readonly'    => true,
					),
					Content::get_json_schema()
				),
			),
			'additionalProperties' => true,
		);
	}
}
