<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Tools\Web_Search_Tool
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types\Tools;

use InvalidArgumentException;

/**
 * Class for a web search tool for a generative model.
 *
 * @since n.e.x.t
 */
final class Web_Search_Tool extends Abstract_Tool {

	/**
	 * Gets the allowed domains for the tool.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The allowed domains.
	 */
	public function get_allowed_domains(): array {
		return $this->to_array()['webSearch']['allowedDomains'];
	}

	/**
	 * Gets the disallowed domains for the tool.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The disallowed domains.
	 */
	public function get_disallowed_domains(): array {
		return $this->to_array()['webSearch']['disallowedDomains'];
	}

	/**
	 * Formats the data for the tool.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The tool data.
	 * @return array<string, mixed> Formatted data.
	 *
	 * @throws InvalidArgumentException Thrown if the tool data is invalid.
	 */
	protected function format_data( array $data ): array {
		if ( isset( $data['webSearch']['allowedDomains'] ) && ! is_array( $data['webSearch']['allowedDomains'] ) ) {
			throw new InvalidArgumentException( 'The allowedDomains value for the web search tool data must be an array of strings.' );
		}
		if ( isset( $data['webSearch']['disallowedDomains'] ) && ! is_array( $data['webSearch']['disallowedDomains'] ) ) {
			throw new InvalidArgumentException( 'The disallowedDomains value for the web search tool data must be an array of strings.' );
		}

		return array(
			'webSearch' => array(
				'allowedDomains'    => isset( $data['webSearch']['allowedDomains'] ) ? array_values( array_filter( $data['webSearch']['allowedDomains'], 'is_string' ) ) : array(),
				'disallowedDomains' => isset( $data['webSearch']['disallowedDomains'] ) ? array_values( array_filter( $data['webSearch']['disallowedDomains'], 'is_string' ) ) : array(),
			),
		);
	}

	/**
	 * Gets the default data for the tool.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Default data.
	 */
	protected function get_default_data(): array {
		return array(
			'webSearch' => array(
				'allowedDomains'    => array(),
				'disallowedDomains' => array(),
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
				'webSearch' => array(
					'type'       => 'object',
					'properties' => array(
						'allowedDomains'    => array(
							'description' => __( 'Web search allowed domains for the tool.', 'ai-services' ),
							'type'        => 'array',
							'items'       => array(
								'type' => 'string',
							),
						),
						'disallowedDomains' => array(
							'description' => __( 'Web search disallowed domains for the tool.', 'ai-services' ),
							'type'        => 'array',
							'items'       => array(
								'type' => 'string',
							),
						),
					),
				),
			),
			'additionalProperties' => false,
		);
	}
}
