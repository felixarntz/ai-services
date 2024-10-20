<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Generation_Config
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types;

use Felix_Arntz\AI_Services\Services\Util\Strings;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use InvalidArgumentException;

/**
 * Class representing configuration options for a generative AI model.
 *
 * @since n.e.x.t
 */
final class Generation_Config implements Arrayable {

	/**
	 * The sanitized configuration arguments.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $sanitized_args;

	/**
	 * Any additional arguments, unsanitized.
	 *
	 * These are not used directly by the class, but are passed through to the API.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $additional_args;

	/**
	 * Type definitions for the supported arguments.
	 *
	 * @since n.e.x.t
	 * @var array<string, string>
	 */
	private $supported_args = array(
		'stopSequences'    => 'array',
		'responseMimeType' => 'string',
		'responseSchema'   => 'object',
		'candidateCount'   => 'integer',
		'maxOutputTokens'  => 'integer',
		'temperature'      => 'float',
		'topP'             => 'float',
		'topK'             => 'integer',
		'presencePenalty'  => 'float',
		'frequencyPenalty' => 'float',
		'responseLogprobs' => 'boolean',
		'logprobs'         => 'integer',
	);

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $args The configuration arguments.
	 */
	public function __construct( array $args ) {
		$args = $this->sanitize_args( $args );

		$this->sanitized_args  = $args['sanitized'];
		$this->additional_args = $args['additional'];
	}

	/**
	 * Returns the stop sequences.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The stop sequences.
	 */
	public function get_stop_sequences(): array {
		return $this->sanitized_args['stopSequences'] ?? null;
	}

	/**
	 * Returns the response MIME type.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The response MIME type.
	 */
	public function get_response_mime_type(): string {
		return $this->sanitized_args['responseMimeType'] ?? null;
	}

	/**
	 * Returns the response schema.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The response schema.
	 */
	public function get_response_schema(): array {
		return $this->sanitized_args['responseSchema'] ?? null;
	}

	/**
	 * Returns the candidate count.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The candidate count.
	 */
	public function get_candidate_count(): int {
		return $this->sanitized_args['candidateCount'] ?? null;
	}

	/**
	 * Returns the maximum output tokens.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The maximum output tokens.
	 */
	public function get_max_output_tokens(): int {
		return $this->sanitized_args['maxOutputTokens'] ?? null;
	}

	/**
	 * Returns the temperature.
	 *
	 * @since n.e.x.t
	 *
	 * @return float The temperature.
	 */
	public function get_temperature(): float {
		return $this->sanitized_args['temperature'] ?? null;
	}

	/**
	 * Returns the top P.
	 *
	 * @since n.e.x.t
	 *
	 * @return float The top P.
	 */
	public function get_top_p(): float {
		return $this->sanitized_args['topP'] ?? null;
	}

	/**
	 * Returns the top K.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The top K.
	 */
	public function get_top_k(): int {
		return $this->sanitized_args['topK'] ?? null;
	}

	/**
	 * Returns the presence penalty.
	 *
	 * @since n.e.x.t
	 *
	 * @return float The presence penalty.
	 */
	public function get_presence_penalty(): float {
		return $this->sanitized_args['presencePenalty'] ?? null;
	}

	/**
	 * Returns the frequency penalty.
	 *
	 * @since n.e.x.t
	 *
	 * @return float The frequency penalty.
	 */
	public function get_frequency_penalty(): float {
		return $this->sanitized_args['frequencyPenalty'] ?? null;
	}

	/**
	 * Returns the response logprobs.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool The response logprobs.
	 */
	public function get_response_logprobs(): bool {
		return $this->sanitized_args['responseLogprobs'] ?? null;
	}

	/**
	 * Returns the logprobs.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The logprobs.
	 */
	public function get_logprobs(): int {
		return $this->sanitized_args['logprobs'] ?? null;
	}

	/**
	 * Returns the additional arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The additional arguments.
	 */
	public function get_additional_args(): array {
		return $this->additional_args;
	}

	/**
	 * Returns the array representation.
	 *
	 * @since n.e.x.t
	 *
	 * @return mixed[] Array representation.
	 */
	public function to_array(): array {
		return $this->sanitized_args + $this->additional_args;
	}

	/**
	 * Creates a Generation_Config instance from an array of content data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $data The content data.
	 * @return Generation_Config Generation_Config instance.
	 *
	 * @throws InvalidArgumentException Thrown if the data is missing required fields.
	 */
	public static function from_array( array $data ): Generation_Config {
		return new Generation_Config( $data );
	}

	/**
	 * Sanitizes the given arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $args The arguments to sanitize.
	 * @return array<string, array<string, mixed>> Associative array with keys 'sanitized' and 'additional', each
	 *                                             containing an array of arguments. The 'sanitized' array contains the
	 *                                             supported sanitized arguments, while the 'additional' array contains
	 *                                             any additional arguments that are not supported, but can be passed
	 *                                             through to the API.
	 */
	private function sanitize_args( array $args ): array {
		$sanitized  = array();
		$additional = array();

		foreach ( $args as $key => $value ) {
			if ( isset( $this->supported_args[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_arg( $value, $this->supported_args[ $key ] );
				continue;
			}

			if ( str_contains( $key, '_' ) ) {
				$camelcase_key = Strings::snake_case_to_camel_case( $key );
				if ( isset( $this->supported_args[ $camelcase_key ] ) ) {
					$sanitized[ $camelcase_key ] = $this->sanitize_arg( $value, $this->supported_args[ $camelcase_key ] );
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
	 * Sanitizies the given value based on the given type.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed  $value The value to sanitize.
	 * @param string $type  The type to sanitize the value to. Must be one of 'array', 'string', 'object', 'integer',
	 *                      'float', or 'boolean'.
	 * @return mixed The sanitized value.
	 *
	 * @throws InvalidArgumentException Thrown if the type is not supported.
	 */
	private function sanitize_arg( $value, string $type ) {
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
