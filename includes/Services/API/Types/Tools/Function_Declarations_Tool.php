<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Tools\Function_Declarations_Tool
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types\Tools;

use InvalidArgumentException;

/**
 * Class for a function declarations tool for a generative model.
 *
 * @since 0.5.0
 */
final class Function_Declarations_Tool extends Abstract_Tool {

	/**
	 * Gets the function declarations from the tool.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed>[] The function declarations.
	 */
	public function get_function_declarations(): array {
		return $this->to_array()['functionDeclarations'];
	}

	/**
	 * Formats the data for the tool.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $data The tool data.
	 * @return array<string, mixed> Formatted data.
	 *
	 * @throws InvalidArgumentException Thrown if the tool data is invalid.
	 */
	protected function format_data( array $data ): array {
		if ( ! isset( $data['functionDeclarations'] ) || ! is_array( $data['functionDeclarations'] ) ) {
			throw new InvalidArgumentException( 'The function declarations tool data must contain an array functionDeclarations value.' );
		}

		foreach ( $data['functionDeclarations'] as &$function_declaration ) {
			if ( ! isset( $function_declaration['name'] ) || ! is_string( $function_declaration['name'] ) ) {
				throw new InvalidArgumentException( 'Each function declaration data must contain a string name value.' );
			}
			if ( isset( $function_declaration['description'] ) && ! is_string( $function_declaration['description'] ) ) {
				throw new InvalidArgumentException( 'The description value of a function declaration must be a string.' );
			}
			if ( isset( $function_declaration['parameters'] ) && ! is_array( $function_declaration['parameters'] ) ) {
				throw new InvalidArgumentException( 'The parameters value of a function declaration must be an object / associative array.' );
			}

			$function_declaration['parameters'] = $this->sanitize_parameters( $function_declaration['parameters'] );
		}

		return array(
			'functionDeclarations' => $data['functionDeclarations'],
		);
	}

	/**
	 * Gets the default data for the tool.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> Default data.
	 */
	protected function get_default_data(): array {
		return array(
			'functionDeclarations' => array(),
		);
	}

	/**
	 * Sanitizes the parameters schema, ensuring every object property is required and additional properties are disallowed.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $schema The schema to sanitize.
	 * @return array<string, mixed> Sanitized schema.
	 */
	protected function sanitize_parameters( array $schema ): array {
		// Every schema must have a type, but that will be checked elsewhere so we can ignore it here.
		if ( ! isset( $schema['type'] ) ) {
			return $schema;
		}

		$type = (array) $schema['type'];
		if ( in_array( 'object', $type, true ) ) {
			if ( isset( $schema['properties'] ) ) {
				$schema['required'] = array_keys( $schema['properties'] );
				foreach ( $schema['properties'] as $key => $child_schema ) {
					$schema['properties'][ $key ] = $this->sanitize_parameters( $child_schema );
				}
			}
			$schema['additionalProperties'] = false;
		}

		if ( in_array( 'array', $type, true ) && isset( $schema['items'] ) ) {
			$schema['items'] = $this->sanitize_parameters( $schema['items'] );
		}

		return $schema;
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
				'functionDeclarations' => array(
					'description' => __( 'Function declarations for the tool.', 'ai-services' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'name'        => array(
								'description' => __( 'Name of the function.', 'ai-services' ),
								'type'        => 'string',
							),
							'description' => array(
								'description' => __( 'Description of the function.', 'ai-services' ),
								'type'        => 'string',
							),
							'parameters'  => array(
								'description'          => __( 'Supported parameters of the function, as an object in JSON schema.', 'ai-services' ),
								'type'                 => 'object',
								'additionalProperties' => true,
							),
						),
					),
				),
			),
			'additionalProperties' => false,
		);
	}
}
