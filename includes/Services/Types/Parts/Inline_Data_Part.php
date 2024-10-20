<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Parts\Inline_Data_Part
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types\Parts;

use InvalidArgumentException;

/**
 * Class for an inline data part of content for a generative model.
 *
 * @since 0.1.0
 */
final class Inline_Data_Part extends Abstract_Part {

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
		if ( ! isset( $data['inlineData'] ) || ! is_array( $data['inlineData'] ) ) {
			throw new InvalidArgumentException( 'The inline data part data must contain an associative array inlineData value.' );
		}

		$inline_data = $data['inlineData'];

		if ( ! isset( $inline_data['mimeType'] ) || ! is_string( $inline_data['mimeType'] ) ) {
			throw new InvalidArgumentException( 'The inline data part data must contain a string mimeType value.' );
		}

		if ( ! isset( $inline_data['data'] ) || ! is_string( $inline_data['data'] ) ) {
			throw new InvalidArgumentException( 'The inline data part data must contain a string data value.' );
		}

		return array(
			'inlineData' => array(
				'mimeType' => $inline_data['mimeType'],
				'data'     => $inline_data['data'],
			),
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
			'inlineData' => array(
				'mimeType' => '',
				'data'     => '',
			),
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
				'inlineData' => array(
					'description' => __( 'Inline data as part of the prompt, such as a file.', 'ai-services' ),
					'type'        => 'object',
					'properties'  => array(
						'mimeType' => array(
							'description' => __( 'MIME type of the inline data.', 'ai-services' ),
							'type'        => 'string',
						),
						'data'     => array(
							'description' => __( 'Base64-encoded data.', 'ai-services' ),
							'type'        => 'string',
						),
					),
				),
			),
			'additionalProperties' => false,
		);
	}
}
