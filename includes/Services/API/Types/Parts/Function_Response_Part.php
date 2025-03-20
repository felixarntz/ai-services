<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Response_Part
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types\Parts;

use InvalidArgumentException;

/**
 * Class for a function response part of content for a generative model.
 *
 * @since 0.5.0
 */
final class Function_Response_Part extends Abstract_Part {

	/**
	 * Gets the ID of the function response from the part.
	 *
	 * If present, this must match the function call ID.
	 * Every function response must have at least one of 'id' or 'name' present.
	 *
	 * @since 0.5.0
	 *
	 * @return string The function response ID, or empty string if none set.
	 */
	public function get_id(): string {
		$data = $this->to_array();
		if ( ! isset( $data['functionResponse']['id'] ) ) {
			return '';
		}
		return $data['functionResponse']['id'];
	}

	/**
	 * Gets the function name from the part.
	 *
	 * If present, this must match the name of the function called.
	 * Every function response must have at least one of 'id' or 'name' present.
	 *
	 * @since 0.5.0
	 *
	 * @return string The function name, or empty string if none set.
	 */
	public function get_name(): string {
		$data = $this->to_array();
		if ( ! isset( $data['functionResponse']['name'] ) ) {
			return '';
		}
		return $data['functionResponse']['name'];
	}

	/**
	 * Gets the function output response from the part.
	 *
	 * @since 0.5.0
	 *
	 * @return mixed The function output response.
	 */
	public function get_response() {
		return $this->to_array()['functionResponse']['response'];
	}

	/**
	 * Formats the data for the part.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $data The part data.
	 * @return array<string, mixed> Formatted data.
	 *
	 * @throws InvalidArgumentException Thrown if the part data is invalid.
	 */
	protected function format_data( array $data ): array {
		if ( ! isset( $data['functionResponse'] ) || ! is_array( $data['functionResponse'] ) ) {
			throw new InvalidArgumentException( 'The function response part data must contain an associative array functionResponse value.' );
		}

		$function_response = $data['functionResponse'];

		if (
			( ! isset( $function_response['id'] ) || ! is_string( $function_response['id'] ) ) &&
			( ! isset( $function_response['name'] ) || ! is_string( $function_response['name'] ) )
		) {
			throw new InvalidArgumentException( 'The function response part data must contain either a string id value or a string name value.' );
		}

		if ( ! isset( $function_response['response'] ) ) {
			throw new InvalidArgumentException( 'The function response part data must contain a response value.' );
		}

		$function_response_formatted = array();
		if ( isset( $function_response['id'] ) ) {
			$function_response_formatted['id'] = $function_response['id'];
		}
		if ( isset( $function_response['name'] ) ) {
			$function_response_formatted['name'] = $function_response['name'];
		}
		$function_response_formatted['response'] = $function_response['response'];

		return array(
			'functionResponse' => $function_response_formatted,
		);
	}

	/**
	 * Gets the default data for the part.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> Default data.
	 */
	protected function get_default_data(): array {
		return array(
			'functionResponse' => array(
				'name'     => '',
				'response' => null,
			),
		);
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
				'functionResponse' => array(
					'description' => __( 'Function response as part of the prompt.', 'ai-services' ),
					'type'        => 'object',
					'properties'  => array(
						'id'       => array(
							'description' => __( 'ID of the function response. If present, it must match the function call ID. Either this or a name must be present.', 'ai-services' ),
							'type'        => 'string',
						),
						'name'     => array(
							'description' => __( 'Name of the function called. Either this or a name must be present.', 'ai-services' ),
							'type'        => 'string',
						),
						'response' => array(
							'description'          => __( 'Response from the function called.', 'ai-services' ),
							'type'                 => array( 'string', 'number', 'boolean', 'array', 'object' ),
							'additionalProperties' => true,
						),
					),
				),
			),
			'additionalProperties' => false,
		);
	}
}
