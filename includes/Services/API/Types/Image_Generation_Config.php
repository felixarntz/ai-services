<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use InvalidArgumentException;

/**
 * Class representing image configuration options for a generative AI model.
 *
 * @since 0.5.0
 */
class Image_Generation_Config extends Generation_Config {

	/**
	 * Constructor.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $args The configuration arguments.
	 */
	public function __construct( array $args ) {
		$this->supported_args = array(
			'responseMimeType' => 'string',
			'candidateCount'   => 'integer',
			'aspectRatio'      => 'string',
			'responseType'     => 'string',
		);

		parent::__construct( $args );
	}

	/**
	 * Returns the response MIME type.
	 *
	 * @since 0.5.0
	 *
	 * @return string The response MIME type, or empty string if not set.
	 */
	public function get_response_mime_type(): string {
		return $this->sanitized_args['responseMimeType'] ?? '';
	}

	/**
	 * Returns the candidate count.
	 *
	 * @since 0.5.0
	 *
	 * @return int The candidate count (default 1).
	 */
	public function get_candidate_count(): int {
		return $this->sanitized_args['candidateCount'] ?? 1;
	}

	/**
	 * Returns the aspect ratio.
	 *
	 * @since 0.5.0
	 *
	 * @return string The aspect ratio, or empty string if not set.
	 */
	public function get_aspect_ratio(): string {
		return $this->sanitized_args['aspectRatio'] ?? '';
	}

	/**
	 * Returns the response type.
	 *
	 * @since 0.5.0
	 *
	 * @return string The quality, or empty string if not set.
	 */
	public function get_response_type(): string {
		return $this->sanitized_args['responseType'] ?? 'inline_data';
	}

	/**
	 * Creates a Generation_Config instance from an array of content data.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Generation_Config Generation_Config instance.
	 *
	 * @phpstan-return Image_Generation_Config
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Generation_Config {
		return new Image_Generation_Config( $data );
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
				'responseMimeType' => array(
					'description' => __( 'MIME type of the generated candidate text.', 'ai-services' ),
					'type'        => 'string',
					'enum'        => array( 'image/png', 'image/jpeg' ),
				),
				'candidateCount'   => array(
					'description' => __( 'Number of image candidates to generate.', 'ai-services' ),
					'type'        => 'integer',
					'minimum'     => 1,
				),
				'aspectRatio'      => array(
					'description' => __( 'Aspect ratio of the generated image.', 'ai-services' ),
					'type'        => 'string',
					'enum'        => array( '1:1', '16:9', '9:16', '4:3', '3:4' ),
				),
				'responseType'     => array(
					'description' => __( 'Response type in which the image is returned.', 'ai-services' ),
					'type'        => 'string',
					'enum'        => array( 'inline_data', 'file_data' ),
				),
			),
			'additionalProperties' => true,
		);
	}
}
