<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Call_Part
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types\Parts;

use InvalidArgumentException;

/**
 * Class for a function call part of content for a generative model.
 *
 * @since 0.5.0
 */
final class Function_Call_Part extends Abstract_Part {

	/**
	 * Gets the ID of the function call from the part.
	 *
	 * Every function call must have at least one of 'id' or 'name' present.
	 *
	 * @since 0.5.0
	 *
	 * @return string The function call ID, or empty string if none set.
	 */
	public function get_id(): string {
		$data = $this->to_array();
		if ( ! isset( $data['functionCall']['id'] ) ) {
			return '';
		}
		return $data['functionCall']['id'];
	}

	/**
	 * Gets the function name from the part.
	 *
	 * Every function call must have at least one of 'id' or 'name' present.
	 *
	 * @since 0.5.0
	 *
	 * @return string The function name, or empty string if none set.
	 */
	public function get_name(): string {
		$data = $this->to_array();
		if ( ! isset( $data['functionCall']['name'] ) ) {
			return '';
		}
		return $data['functionCall']['name'];
	}

	/**
	 * Gets the function input arguments from the part.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> The function input arguments.
	 */
	public function get_args(): array {
		return $this->to_array()['functionCall']['args'];
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
		if ( ! isset( $data['functionCall'] ) || ! is_array( $data['functionCall'] ) ) {
			throw new InvalidArgumentException( 'The function call part data must contain an associative array functionCall value.' );
		}

		$function_call = $data['functionCall'];

		if (
			( ! isset( $function_call['id'] ) || ! is_string( $function_call['id'] ) ) &&
			( ! isset( $function_call['name'] ) || ! is_string( $function_call['name'] ) )
		) {
			throw new InvalidArgumentException( 'The function call part data must contain either a string id value or a string name value.' );
		}

		if ( ! isset( $function_call['args'] ) || ! is_array( $function_call['args'] ) ) {
			throw new InvalidArgumentException( 'The function call part data must contain an object / associative array args value.' );
		}

		$function_call_formatted = array();
		if ( isset( $function_call['id'] ) ) {
			$function_call_formatted['id'] = $function_call['id'];
		}
		if ( isset( $function_call['name'] ) ) {
			$function_call_formatted['name'] = $function_call['name'];
		}
		$function_call_formatted['args'] = $function_call['args'];

		return array(
			'functionCall' => $function_call_formatted,
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
			'functionCall' => array(
				'name' => '',
				'args' => array(),
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
				'functionCall' => array(
					'description' => __( 'Function call as part of the prompt.', 'ai-services' ),
					'type'        => 'object',
					'properties'  => array(
						'id'   => array(
							'description' => __( 'ID of the function call. Either this or a name must be present.', 'ai-services' ),
							'type'        => 'string',
						),
						'name' => array(
							'description' => __( 'Name of the function to call. Either this or a name must be present.', 'ai-services' ),
							'type'        => 'string',
						),
						'args' => array(
							'description'          => __( 'Arguments input for the function to call.', 'ai-services' ),
							'type'                 => 'object',
							'additionalProperties' => true,
						),
					),
				),
			),
			'additionalProperties' => false,
		);
	}
}
