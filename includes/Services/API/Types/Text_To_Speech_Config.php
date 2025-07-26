<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Text_To_Speech_Config
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\Base\Abstract_Generation_Config;

/**
 * Class representing text-to-speech configuration options for a generative AI model.
 *
 * @since 0.7.0
 */
class Text_To_Speech_Config extends Abstract_Generation_Config {

	/**
	 * Returns the voice identifier.
	 *
	 * @since 0.7.0
	 *
	 * @return string The voice identifier, or empty string if not set.
	 */
	public function get_voice(): string {
		return $this->get_arg( 'voice' );
	}

	/**
	 * Returns the response MIME type.
	 *
	 * @since 0.7.0
	 *
	 * @return string The response MIME type, or empty string if not set.
	 */
	public function get_response_mime_type(): string {
		return $this->get_arg( 'responseMimeType' );
	}

	/**
	 * Gets the definition for the supported arguments.
	 *
	 * @since 0.7.0
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
	 * @since 0.7.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'voice'            => array(
					'description' => __( 'A voice identifier.', 'ai-services' ),
					'type'        => 'string',
				),
				'responseMimeType' => array(
					'description' => __( 'MIME type to control the output format.', 'ai-services' ),
					'type'        => 'string',
					'enum'        => array( 'audio/mpeg', 'audio/wav', 'audio/pcm', 'audio/flac' ),
				),
			),
			'additionalProperties' => true,
		);
	}
}
