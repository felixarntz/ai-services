<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\Base\Abstract_Generation_Config;

/**
 * Class representing image configuration options for a generative AI model.
 *
 * @since 0.5.0
 * @since n.e.x.t Now extends `Abstract_Generation_Config`.
 */
class Image_Generation_Config extends Abstract_Generation_Config {

	/**
	 * Returns the response MIME type.
	 *
	 * @since 0.5.0
	 *
	 * @return string The response MIME type, or empty string if not set.
	 */
	public function get_response_mime_type(): string {
		return $this->get_arg( 'responseMimeType' );
	}

	/**
	 * Returns the candidate count.
	 *
	 * @since 0.5.0
	 *
	 * @return int The candidate count (default 1).
	 */
	public function get_candidate_count(): int {
		return $this->get_arg( 'candidateCount' );
	}

	/**
	 * Returns the aspect ratio.
	 *
	 * @since 0.5.0
	 *
	 * @return string The aspect ratio, or empty string if not set.
	 */
	public function get_aspect_ratio(): string {
		return $this->get_arg( 'aspectRatio' );
	}

	/**
	 * Returns the response type.
	 *
	 * @since 0.5.0
	 *
	 * @return string The quality, or empty string if not set.
	 */
	public function get_response_type(): string {
		return $this->get_arg( 'responseType' );
	}

	/**
	 * Gets the definition for the supported arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The supported arguments definition.
	 */
	protected function get_supported_args_definition(): array {
		$schema = self::get_json_schema();
		return $schema['properties'];
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
					'enum'        => array( 'image/png', 'image/jpeg', 'image/webp' ),
				),
				'candidateCount'   => array(
					'description' => __( 'Number of image candidates to generate.', 'ai-services' ),
					'type'        => 'integer',
					'minimum'     => 1,
					'default'     => 1,
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
					'default'     => 'inline_data',
				),
			),
			'additionalProperties' => true,
		);
	}
}
