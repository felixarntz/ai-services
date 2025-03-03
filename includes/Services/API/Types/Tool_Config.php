<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Tool_Config
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\Contracts\With_JSON_Schema;
use Felix_Arntz\AI_Services\Services\Util\Strings;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

/**
 * Class representing tool configuration for a generative AI model.
 *
 * @since 0.5.0
 */
final class Tool_Config implements Arrayable, With_JSON_Schema {

	/**
	 * The sanitized configuration arguments.
	 *
	 * @since 0.5.0
	 * @var array<string, mixed>
	 */
	private $sanitized_args;

	/**
	 * Type definitions for the supported arguments.
	 *
	 * @since 0.5.0
	 * @var array<string, string>
	 */
	private $supported_args = array(
		'functionCallMode'     => 'string',
		'allowedFunctionNames' => 'array',
	);

	/**
	 * Constructor.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $args The configuration arguments.
	 */
	public function __construct( array $args ) {
		$this->sanitized_args = $this->sanitize_args( $args );
	}

	/**
	 * Returns the function call mode.
	 *
	 * @since 0.5.0
	 *
	 * @return string The function call mode, or empty string if not set.
	 */
	public function get_function_call_mode(): string {
		return $this->sanitized_args['functionCallMode'] ?? '';
	}

	/**
	 * Returns the allowed function names.
	 *
	 * @since 0.5.0
	 *
	 * @return string[] The allowed function names, or empty array if not set.
	 */
	public function get_allowed_function_names(): array {
		return $this->sanitized_args['allowedFunctionNames'] ?? array();
	}

	/**
	 * Returns the array representation.
	 *
	 * @since 0.5.0
	 *
	 * @return mixed[] Array representation.
	 */
	public function to_array(): array {
		return $this->sanitized_args;
	}

	/**
	 * Creates a Tool_Config instance from an array of content data.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Tool_Config Tool_Config instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Tool_Config {
		return new Tool_Config( $data );
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
				'functionCallMode'     => array(
					'description' => __( 'Mode for how to consider function calling.', 'ai-services' ),
					'type'        => 'string',
					'enum'        => array( 'auto', 'any' ),
				),
				'allowedFunctionNames' => array(
					'description' => __( 'List of function names allowed to call.', 'ai-services' ),
					'type'        => 'array',
					'items'       => array( 'type' => 'string' ),
				),
			),
			'additionalProperties' => false,
		);
	}

	/**
	 * Sanitizes the given arguments.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $args The arguments to sanitize.
	 * @return array<string, mixed> Sanitized arguments.
	 */
	private function sanitize_args( array $args ): array {
		$sanitized = array();

		foreach ( $args as $key => $value ) {
			if ( isset( $this->supported_args[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_arg( $value, $this->supported_args[ $key ], $key );
				continue;
			}

			if ( str_contains( $key, '_' ) ) {
				$camelcase_key = Strings::snake_case_to_camel_case( $key );
				if ( isset( $this->supported_args[ $camelcase_key ] ) ) {
					$sanitized[ $camelcase_key ] = $this->sanitize_arg( $value, $this->supported_args[ $camelcase_key ], $camelcase_key );
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitizies the given value based on the given type.
	 *
	 * @since 0.5.0
	 *
	 * @param mixed  $value    The value to sanitize.
	 * @param string $type     The type to sanitize the value to. Must be one of 'array', 'string', 'object',
	 *                         'integer', 'float', or 'boolean'.
	 * @param string $arg_name The name of the argument being sanitized.
	 * @return mixed The sanitized value.
	 *
	 * @throws InvalidArgumentException Thrown if the type is not supported.
	 */
	private function sanitize_arg( $value, string $type, string $arg_name ) {
		if ( 'functionCallMode' === $arg_name && ! in_array( $value, array( 'auto', 'any' ), true ) ) {
			return 'auto';
		}

		switch ( $type ) {
			case 'array':
				if ( ! is_array( $value ) ) {
					if ( ! $value ) {
						return array();
					}
					return array( $value );
				}
				return array_values( $value );
			case 'string':
				return (string) $value;
			case 'object':
				if ( ! is_array( $value ) ) {
					if ( is_object( $value ) ) {
						if ( $value instanceof Arrayable ) {
							return $value->to_array();
						}
						return (array) $value;
					}
					return array();
				}
				return $value;
			case 'integer':
				return (int) $value;
			case 'float':
				return (float) $value;
			case 'boolean':
				return (bool) $value;
			default:
				throw new InvalidArgumentException( 'Unsupported type.' );
		}
	}
}
