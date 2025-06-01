<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API\Types;

use Felix_Arntz\AI_Services\Services\API\Enums\Modality;
use Felix_Arntz\AI_Services\Services\Base\Abstract_Generation_Config;
use InvalidArgumentException;

/**
 * Class representing text configuration options for a generative AI model.
 *
 * @since 0.2.0
 * @since 0.5.0 Renamed from `Generation_Config`.
 * @since n.e.x.t Now extends `Abstract_Generation_Config`.
 */
class Text_Generation_Config extends Abstract_Generation_Config {

	/**
	 * Returns the stop sequences.
	 *
	 * @since 0.2.0
	 *
	 * @return string[] The stop sequences, or empty array if not set.
	 */
	public function get_stop_sequences(): array {
		return $this->get_arg( 'stopSequences' );
	}

	/**
	 * Returns the response MIME type.
	 *
	 * @since 0.2.0
	 *
	 * @return string The response MIME type, or empty string if not set.
	 */
	public function get_response_mime_type(): string {
		return $this->get_arg( 'responseMimeType' );
	}

	/**
	 * Returns the response schema.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, mixed> The response schema, or empty array if not set.
	 */
	public function get_response_schema(): array {
		return $this->get_arg( 'responseSchema' );
	}

	/**
	 * Returns the candidate count.
	 *
	 * @since 0.2.0
	 *
	 * @return int The candidate count (default 1).
	 */
	public function get_candidate_count(): int {
		return $this->get_arg( 'candidateCount' );
	}

	/**
	 * Returns the maximum output tokens.
	 *
	 * @since 0.2.0
	 *
	 * @return int The maximum output tokens, or 0 if not set.
	 */
	public function get_max_output_tokens(): int {
		return $this->get_arg( 'maxOutputTokens' );
	}

	/**
	 * Returns the temperature.
	 *
	 * @since 0.2.0
	 *
	 * @return float The temperature (between 0.0 and 1.0), or 0.0 if not set.
	 */
	public function get_temperature(): float {
		return $this->get_arg( 'temperature' );
	}

	/**
	 * Returns the top P.
	 *
	 * @since 0.2.0
	 *
	 * @return float The top P, or 0.0 if not set.
	 */
	public function get_top_p(): float {
		return $this->get_arg( 'topP' );
	}

	/**
	 * Returns the top K.
	 *
	 * @since 0.2.0
	 *
	 * @return int The top K, or 0 if not set.
	 */
	public function get_top_k(): int {
		return $this->get_arg( 'topK' );
	}

	/**
	 * Returns the presence penalty.
	 *
	 * @since 0.2.0
	 *
	 * @return float The presence penalty, or 0.0 if not set.
	 */
	public function get_presence_penalty(): float {
		return $this->get_arg( 'presencePenalty' );
	}

	/**
	 * Returns the frequency penalty.
	 *
	 * @since 0.2.0
	 *
	 * @return float The frequency penalty, or 0.0 if not set.
	 */
	public function get_frequency_penalty(): float {
		return $this->get_arg( 'frequencyPenalty' );
	}

	/**
	 * Returns whether to include the response logprobs.
	 *
	 * @since 0.2.0
	 *
	 * @return bool Whether to include the response logprobs.
	 */
	public function get_response_logprobs(): bool {
		return $this->get_arg( 'responseLogprobs' );
	}

	/**
	 * Returns the top logprobs.
	 *
	 * @since 0.2.0
	 *
	 * @return int The top logprobs, or 0 if not set.
	 */
	public function get_logprobs(): int {
		return $this->get_arg( 'logprobs' );
	}

	/**
	 * Returns the output modalities.
	 *
	 * @since 0.6.0
	 *
	 * @return string[] The output modalities, or empty array if not set.
	 */
	public function get_output_modalities(): array {
		return $this->get_arg( 'outputModalities' );
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
	 * Sanitizes the given value based on the given type.
	 *
	 * @since n.e.x.t
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
		if ( 'temperature' === $arg_name && ( (float) $value < 0.0 || (float) $value > 1.0 ) ) {
			throw new InvalidArgumentException( 'Temperature must be between 0.0 and 1.0.' );
		}

		return parent::sanitize_arg( $value, $type, $arg_name );
	}

	/**
	 * Returns the JSON schema for the expected input.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, mixed> The JSON schema.
	 */
	public static function get_json_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'stopSequences'    => array(
					'description' => __( 'Set of character sequences that will stop output generation.', 'ai-services' ),
					'type'        => 'array',
					'items'       => array( 'type' => 'string' ),
				),
				'responseMimeType' => array(
					'description' => __( 'MIME type of the generated candidate text.', 'ai-services' ),
					'type'        => 'string',
					'enum'        => array( 'text/plain', 'application/json' ),
				),
				'responseSchema'   => array(
					'description'          => __( 'Output schema of the generated candidate text (only relevant if responseMimeType is application/json).', 'ai-services' ),
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => true,
				),
				'candidateCount'   => array(
					'description' => __( 'Number of response candidates to generate.', 'ai-services' ),
					'type'        => 'integer',
					'minimum'     => 1,
				),
				'maxOutputTokens'  => array(
					'description' => __( 'The maximum number of tokens to include in a response candidate.', 'ai-services' ),
					'type'        => 'integer',
					'minimum'     => 1,
				),
				'temperature'      => array(
					'description' => sprintf(
						/* translators: 1: Minimum value, 2: Maximum value */
						__( 'Floating point value to control the randomness of the output, between %1$s and %2$s.', 'ai-services' ),
						'0.0',
						'1.0'
					),
					'type'        => 'number',
					'minimum'     => 0.0,
					'maximum'     => 1.0,
				),
				'topP'             => array(
					'description' => __( 'The maximum cumulative probability of tokens to consider when sampling.', 'ai-services' ),
					'type'        => 'number',
				),
				'topK'             => array(
					'description' => __( 'The maximum number of tokens to consider when sampling.', 'ai-services' ),
					'type'        => 'integer',
				),
				'presencePenalty'  => array(
					'description' => __( 'Presence penalty applied to the next token’s logprobs if the token has already been seen in the response.', 'ai-services' ),
					'type'        => 'number',
				),
				'frequencyPenalty' => array(
					'description' => __( 'Frequency penalty applied to the next token’s logprobs, multiplied by the number of times each token has been seen in the response so far.', 'ai-services' ),
					'type'        => 'number',
				),
				'responseLogprobs' => array(
					'description' => __( 'Whether to return log probabilities of the output tokens in the response or not.', 'ai-services' ),
					'type'        => 'boolean',
				),
				'logprobs'         => array(
					'description' => __( 'The number of top logprobs to return at each decoding step.', 'ai-services' ),
					'type'        => 'integer',
				),
				'outputModalities' => array(
					'description' => __( 'The modalities that the response can contain.', 'ai-services' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
						'enum' => array(
							Modality::TEXT,
							Modality::IMAGE,
							Modality::AUDIO,
						),
					),
				),
			),
			'additionalProperties' => true,
		);
	}
}
