<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types\Parts;

use InvalidArgumentException;

/**
 * Class for a text part of content for a generative model.
 *
 * @since 0.1.0
 */
final class Text_Part extends Abstract_Part {

	/**
	 * Gets the text from the part.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The text.
	 */
	public function get_text(): string {
		return $this->to_array()['text'];
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
	protected function format_data( array $data ): array {
		if ( ! isset( $data['text'] ) || ! is_string( $data['text'] ) ) {
			throw new InvalidArgumentException( 'The text part data must contain a string text value.' );
		}

		return array(
			'text' => $data['text'],
		);
	}

	/**
	 * Gets the default data for the part.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Default data.
	 */
	protected function get_default_data(): array {
		return array(
			'text' => '',
		);
	}

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'text' => array(
					'description' => __( 'Prompt text content.', 'ai-services' ),
					'type'        => 'string',
				),
			),
			'additionalProperties' => false,
		);
	}
}
