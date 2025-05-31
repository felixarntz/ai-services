<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\OpenAI_Compatible_With_Function_Calling_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Contracts\Tool;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Call_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Response_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Function_Declarations_Tool;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use InvalidArgumentException;

/**
 * Trait for an OpenAI compatible model which implements function calling.
 *
 * @since n.e.x.t
 */
trait OpenAI_Compatible_With_Function_Calling_Trait {
	use Model_Param_Tool_Config_Trait;
	use Model_Param_Tools_Trait;

	/**
	 * Prepares the API request parameters for generating text content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $contents The contents to generate text for.
	 * @return array<string, mixed> The parameters for generating text content.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid tool is provided.
	 */
	protected function prepare_generate_text_params( array $contents ): array {
		$params = parent::prepare_generate_text_params( $contents );

		if ( $this->get_tools() ) {
			foreach ( $this->get_tools() as $tool ) {
				$prepared = $this->prepare_tool( $params, $tool );
				if ( ! $prepared ) {
					throw $this->get_api_client()->create_bad_request_exception(
						'Only function declarations tools are supported.'
					);
				}
			}
		}

		if ( $this->get_tool_config() ) {
			$params['tool_choice'] = $this->prepare_tool_choice_param( $this->get_tool_config() );
		}

		return $params;
	}

	/**
	 * Transforms a given candidate from the API response into a Parts instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $candidate_data The API response candidate data.
	 * @return Parts The Parts instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	protected function prepare_response_candidate_content_parts( array $candidate_data ): Parts {
		$parts = parent::prepare_response_candidate_content_parts( $candidate_data );

		if ( isset( $candidate_data['message']['tool_calls'] ) && is_array( $candidate_data['message']['tool_calls'] ) ) {
			foreach ( $candidate_data['message']['tool_calls'] as $tool_call ) {
				$prepared = $this->prepare_response_message_tool_call( $parts, $tool_call );
				if ( ! $prepared ) {
					throw $this->get_api_client()->create_response_exception(
						'The response includes a tool call of an unexpected type.'
					);
				}
			}
		}

		return $parts;
	}

	/**
	 * Prepares a given tool call from the response message, amending the provided Parts instance as needed.
	 *
	 * @since n.e.x.t
	 *
	 * @param Parts                $parts          The Parts instance to amend.
	 * @param array<string, mixed> $tool_call_data The tool call data from the response message.
	 * @return bool True if the tool call was successfully prepared, false otherwise.
	 */
	protected function prepare_response_message_tool_call( Parts $parts, array $tool_call_data ): bool {
		if ( ! isset( $tool_call_data['type'] ) || 'function' !== $tool_call_data['type'] || ! isset( $tool_call_data['function'] ) ) {
			return false;
		}

		$parts->add_function_call_part(
			$tool_call_data['id'],
			$tool_call_data['function']['name'],
			is_string( $tool_call_data['function']['arguments'] )
				? json_decode( $tool_call_data['function']['arguments'], true )
				: $tool_call_data['function']['arguments']
		);

		return true;
	}

	/**
	 * Prepares a single tool for the API request, amending the provided parameters as needed.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $params The parameters to prepare the tools for. Passed by reference.
	 * @param Tool                 $tool   The tool to prepare.
	 * @return bool True if the tool was successfully prepared, false otherwise.
	 */
	protected function prepare_tool( array &$params, Tool $tool ): bool {
		if ( ! $tool instanceof Function_Declarations_Tool ) {
			return false;
		}

		$function_declarations = $tool->get_function_declarations();

		if ( count( $function_declarations ) > 0 ) {
			if ( ! isset( $params['tools'] ) ) {
				$params['tools'] = array();
			}
			foreach ( $function_declarations as $declaration ) {
				$params['tools'][] = array(
					'type'     => 'function',
					'function' => array_filter(
						array(
							'name'        => $declaration['name'],
							'description' => $declaration['description'] ?? null,
							'parameters'  => $declaration['parameters'] ?? null,
						)
					),
				);
			}
		}

		return true;
	}

	/**
	 * Prepares the API request tool choice parameter for the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param Tool_Config $tool_config The tool config to prepare the parameter with.
	 * @return array<string, mixed> The tool config parameter value.
	 */
	private function prepare_tool_choice_param( Tool_Config $tool_config ): array {
		// Either 'auto' or 'any'.
		$tool_choice_param = $tool_config->get_function_call_mode() === 'any' ? 'required' : 'auto';

		if ( 'required' === $tool_choice_param ) {
			// If one specific function must be called, the parameter needs to be an object, otherwise a string.
			$allowed_function_names = $tool_config->get_allowed_function_names();
			if ( count( $allowed_function_names ) === 1 ) {
				$tool_choice_param = array(
					'type'     => 'function',
					'function' => array( 'name' => $allowed_function_names[0] ),
				);
			}
		}

		return $tool_choice_param;
	}

	/**
	 * Gets the content transformers.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The content transformers.
	 *
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	protected function get_content_transformers(): array {
		$api_client = $this->get_api_client();

		$transformers = parent::get_content_transformers();

		$orig_role_transformer    = $transformers['role'];
		$orig_content_transformer = $transformers['content'];

		$transformers['role'] = static function ( Content $content ) use ( $orig_role_transformer ) {
			// Special case of a function response.
			$parts = $content->get_parts();
			if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Function_Response_Part ) {
				return 'tool';
			}

			return $orig_role_transformer( $content );
		};

		$transformers['content'] = static function ( Content $content ) use ( $orig_content_transformer, $api_client ) {
			// Special case of a function response.
			$parts = $content->get_parts();
			if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Function_Response_Part ) {
				$response = $parts->get( 0 )->get_response();
				return json_encode( $response ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			}

			$sanitized_parts = new Parts();
			foreach ( $parts as $part ) {
				/*
				 * Special cases: Function call parts are handled as part of a separate `tool_calls` key, and
				 * function response parts are are only supported as the only content of a message. They are
				 * handled as a special case above.
				 */
				if ( $part instanceof Function_Response_Part ) {
					throw $api_client->create_bad_request_exception(
						'The API only allows a single function response, and it has to be the only content of the message.'
					);
				}

				if ( $part instanceof Function_Call_Part ) {
					// Skip function call parts, they are handled in a separate `tool_calls` key.
					continue;
				}

				$sanitized_parts->add_part( $part );
			}
			$sanitized_content = new Content( $content->get_role(), $sanitized_parts );

			return $orig_content_transformer( $sanitized_content );
		};

		$transformers['tool_calls'] = static function ( Content $content ) {
			// Special key that only applies in case function calls are present.
			$tool_calls = array();
			foreach ( $content->get_parts() as $part ) {
				if ( $part instanceof Function_Call_Part ) {
					$tool_calls[] = array(
						'type'     => 'function',
						'id'       => $part->get_id(),
						'function' => array(
							'name'      => $part->get_name(),
							'arguments' => json_encode( $part->get_args() ), // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
						),
					);
				}
			}
			if ( count( $tool_calls ) > 0 ) {
				return $tool_calls;
			}
			return null;
		};

		$transformers['tool_call_id'] = static function ( Content $content ) {
			// Special key that only applies in case of a function response.
			$parts = $content->get_parts();
			if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Function_Response_Part ) {
				return $parts->get( 0 )->get_id();
			}
			return null;
		};

		return $transformers;
	}
}
