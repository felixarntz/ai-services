<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Base\Abstract_Generation_Config
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Base;

use Felix_Arntz\AI_Services\Services\Contracts\Generation_Config;
use Felix_Arntz\AI_Services\Services\Util\Strings;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

/**
 * Base class representing configuration options for a generative AI model.
 *
 * @since 0.2.0 Originally implemented as non-abstract class `Types\Generation_Config`.
 * @since 0.7.0
 */
abstract class Abstract_Generation_Config implements Generation_Config {

	/**
	 * The sanitized configuration arguments.
	 *
	 * @since 0.2.0
	 * @var array<string, mixed>
	 */
	private $sanitized_args;

	/**
	 * Any additional arguments, unsanitized.
	 *
	 * These are not used directly by the class, but are passed through to the API.
	 *
	 * @since 0.2.0
	 * @var array<string, mixed>
	 */
	private $additional_args;

	/**
	 * Default values for the sanitized configuration arguments.
	 *
	 * @since 0.7.0
	 * @var array<string, mixed>
	 */
	private $defaults;

	/**
	 * Constructor.
	 *
	 * @since 0.2.0
	 *
	 * @param array<string, mixed> $args The configuration arguments.
	 */
	final public function __construct( array $args ) {
		$args_definition = $this->get_supported_args_definition();

		$args = $this->sanitize_args( $args, $args_definition );

		$this->sanitized_args  = $args['sanitized'];
		$this->additional_args = $args['additional'];
		$this->defaults        = $this->get_defaults( $args_definition );
	}

	/**
	 * Returns the value for the given supported argument.
	 *
	 * @since 0.7.0
	 *
	 * @param string $name The argument name.
	 * @return mixed The argument value, or its default value if not set.
	 */
	final public function get_arg( string $name ) {
		if ( ! isset( $this->sanitized_args[ $name ] ) ) {
			return $this->defaults[ $name ] ?? null;
		}

		return $this->sanitized_args[ $name ];
	}

	/**
	 * Returns all formally supported arguments.
	 *
	 * Only includes arguments that have an explicit value set, i.e. not defaults.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, mixed> The arguments.
	 */
	final public function get_args(): array {
		return $this->sanitized_args;
	}

	/**
	 * Returns the additional arguments.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, mixed> The additional arguments.
	 */
	final public function get_additional_args(): array {
		return $this->additional_args;
	}

	/**
	 * Returns the array representation.
	 *
	 * @since 0.2.0
	 *
	 * @return mixed[] Array representation.
	 */
	final public function to_array(): array {
		return $this->sanitized_args + $this->additional_args;
	}

	/**
	 * Creates a Generation_Config instance from an array of content data.
	 *
	 * @since 0.2.0
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return static Generation_Config instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): static {
		return new static( $data );
	}

	/**
	 * Gets the definition for the supported arguments.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, mixed> The supported arguments definition.
	 */
	abstract protected function get_supported_args_definition(): array;

	/**
	 * Gets the default values for the supported arguments.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $args_definition The arguments definition.
	 * @return array<string, mixed> The default values.
	 */
	private function get_defaults( array $args_definition ): array {
		$defaults = array();

		foreach ( $args_definition as $key => $definition ) {
			if ( isset( $definition['default'] ) ) {
				$defaults[ $key ] = $definition['default'];
			} elseif ( isset( $definition['type'] ) ) {
				// Set default to type-safe value that is considered false-y.
				switch ( $definition['type'] ) {
					case 'array':
						$defaults[ $key ] = array();
						break;
					case 'string':
						$defaults[ $key ] = '';
						break;
					case 'object':
						$defaults[ $key ] = array();
						break;
					case 'integer':
						$defaults[ $key ] = 0;
						break;
					case 'number':
					case 'float':
						$defaults[ $key ] = 0.0;
						break;
					case 'boolean':
						$defaults[ $key ] = false;
						break;
				}
			}
		}

		return $defaults;
	}

	/**
	 * Sanitizes the given arguments.
	 *
	 * @since 0.2.0
	 * @since 0.7.0 The $args_definition parameter was added.
	 *
	 * @param array<string, mixed> $args            The arguments to sanitize.
	 * @param array<string, mixed> $args_definition The arguments definition.
	 * @return array<string, array<string, mixed>>  Associative array with keys 'sanitized' and 'additional', each
	 *                                              containing an array of arguments. The 'sanitized' array contains
	 *                                              the supported sanitized arguments, while the 'additional' array
	 *                                              contains any additional arguments that are not supported, but can
	 *                                              be passed through to the API.
	 */
	private function sanitize_args( array $args, array $args_definition ): array {
		$sanitized  = array();
		$additional = array();

		foreach ( $args as $key => $value ) {
			if ( isset( $args_definition[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_arg( $value, $args_definition[ $key ]['type'] ?? 'string', $key );
				continue;
			}

			if ( str_contains( $key, '_' ) ) {
				$camelcase_key = Strings::snake_case_to_camel_case( $key );
				if ( isset( $args_definition[ $camelcase_key ] ) ) {
					$sanitized[ $camelcase_key ] = $this->sanitize_arg( $value, $args_definition[ $camelcase_key ]['type'] ?? 'string', $camelcase_key );
					continue;
				}
			}

			$additional[ $key ] = $value;
		}

		return array(
			'sanitized'  => $sanitized,
			'additional' => $additional,
		);
	}

	/**
	 * Sanitizes the given value based on the given type.
	 *
	 * @since 0.2.0
	 *
	 * @param mixed  $value    The value to sanitize.
	 * @param string $type     The type to sanitize the value to. Must be one of 'array', 'string', 'object',
	 *                         'integer', 'float', or 'boolean'.
	 * @param string $arg_name The name of the argument being sanitized.
	 * @return mixed The sanitized value.
	 *
	 * @throws InvalidArgumentException Thrown if the type is not supported or the value is invalid.
	 */
	protected function sanitize_arg( $value, string $type, string $arg_name ) {
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
			case 'number':
			case 'float':
				return (float) $value;
			case 'boolean':
				return (bool) $value;
			default:
				throw new InvalidArgumentException( 'Unsupported type.' );
		}
	}
}
