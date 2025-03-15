<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Google_AI_Text_Generation_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google;

use Felix_Arntz\AI_Services\Google\Types\Safety_Setting;
use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Call_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Response_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Function_Declarations_Tool;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History;
use Felix_Arntz\AI_Services\Services\Contracts\With_Function_Calling;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_Chat_History_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use Generator;
use InvalidArgumentException;

/**
 * Class representing a Google text generation AI model.
 *
 * @since 0.1.0
 * @since 0.5.0 Renamed from `Google_AI_Model`.
 */
class Google_AI_Text_Generation_Model extends Abstract_AI_Model implements With_Text_Generation, With_Chat_History, With_Function_Calling, With_Multimodal_Input {
	use With_Text_Generation_Trait;
	use With_Chat_History_Trait;

	/**
	 * The Google AI API instance.
	 *
	 * @since 0.1.0
	 * @var Google_AI_API_Client
	 */
	protected $api;

	/**
	 * The tools available to use for the model.
	 *
	 * @since 0.5.0
	 * @var Tools|null
	 */
	protected $tools;

	/**
	 * The tool configuration, if applicable.
	 *
	 * @since 0.5.0
	 * @var Tool_Config|null
	 */
	protected $tool_config;

	/**
	 * The generation configuration.
	 *
	 * @since 0.1.0
	 * @var Text_Generation_Config|null
	 */
	protected $generation_config;

	/**
	 * The safety settings.
	 *
	 * @since 0.1.0
	 * @var Safety_Setting[]
	 */
	protected $safety_settings;

	/**
	 * The system instruction.
	 *
	 * @since 0.1.0
	 * @var Content|null
	 */
	protected $system_instruction;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Google_AI_API_Client $api             The Google AI API instance.
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see Google_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Google_AI_API_Client $api, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->api = $api;

		parent::__construct( $model, $model_params, $request_options );
	}

	/**
	 * Sets the model parameters on the class instance.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	protected function set_model_params( array $model_params ): void {
		$this->set_props_from_args(
			array(
				array(
					'arg_key'           => 'tools',
					'property_name'     => 'tools',
					'sanitize_callback' => static function ( $tools ) {
						if ( $tools instanceof Tools ) {
							return $tools;
						}
						return Tools::from_array( $tools );
					},
				),
				array(
					'arg_key'           => 'toolConfig',
					'property_name'     => 'tool_config',
					'sanitize_callback' => static function ( $tool_config ) {
						if ( $tool_config instanceof Tool_Config ) {
							return $tool_config;
						}
						return Tool_Config::from_array( $tool_config );
					},
				),
				array(
					'arg_key'           => 'generationConfig',
					'property_name'     => 'generation_config',
					'sanitize_callback' => static function ( $generation_config ) {
						if ( $generation_config instanceof Text_Generation_Config ) {
							return $generation_config;
						}
						return Text_Generation_Config::from_array( $generation_config );
					},
				),
			),
			$model_params
		);

		if ( isset( $model_params['systemInstruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['systemInstruction'] );
		}

		if ( isset( $model_params['safetySettings'] ) ) {
			foreach ( $model_params['safetySettings'] as $safety_setting ) {
				if ( ! $safety_setting instanceof Safety_Setting ) {
					throw new InvalidArgumentException(
						esc_html__( 'The safetySettings parameter must contain Safety_Setting instances.', 'ai-services' )
					);
				}
			}
			$this->safety_settings = $model_params['safetySettings'];
		} else {
			$this->safety_settings = array();
		}
	}

	/**
	 * Sends a request to generate text content.
	 *
	 * @since 0.1.0
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_generate_text_request( array $contents, array $request_options ): Candidates {
		$params = $this->prepare_generate_text_params( $contents );

		$request  = $this->api->create_generate_content_request(
			$this->get_model_slug(),
			$params,
			array_merge(
				$this->get_request_options(),
				$request_options
			)
		);
		$response = $this->api->make_request( $request );

		return $this->api->process_response_data(
			$response,
			function ( $response_data ) {
				return $this->get_response_candidates( $response_data );
			}
		);
	}

	/**
	 * Sends a request to generate text content, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Generator<Candidates> Generator that yields the chunks of response candidates with generated text
	 *                               content - usually just one candidate.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_stream_generate_text_request( array $contents, array $request_options ): Generator {
		$params = $this->prepare_generate_text_params( $contents );

		$request  = $this->api->create_stream_generate_content_request(
			$this->get_model_slug(),
			$params,
			array_merge(
				$this->get_request_options(),
				$request_options
			)
		);
		$response = $this->api->make_request( $request );

		return $this->api->process_response_stream(
			$response,
			function ( $response_data, $prev_chunk_candidates ) {
				return $this->get_response_candidates( $response_data, $prev_chunk_candidates );
			}
		);
	}

	/**
	 * Prepares the API request parameters for generating text content.
	 *
	 * @since 0.3.0
	 *
	 * @param Content[] $contents The contents to generate text for.
	 * @return array<string, mixed> The parameters for generating text content.
	 */
	private function prepare_generate_text_params( array $contents ): array {
		$transformers = self::get_content_transformers();

		$params = array(
			'contents' => array_map(
				static function ( Content $content ) use ( $transformers ) {
					return Transformer::transform_content( $content, $transformers );
				},
				$contents
			),
		);

		if ( $this->tools ) {
			$params['tools'] = $this->prepare_tools_param( $this->tools );
		}

		if ( $this->tool_config ) {
			$params['toolConfig'] = $this->prepare_tool_config_param( $this->tool_config );
		}

		if ( $this->generation_config ) {
			$params                     = array_merge( $this->generation_config->get_additional_args(), $params );
			$params['generationConfig'] = Transformer::transform_generation_config_params(
				array(),
				$this->generation_config,
				self::get_generation_config_transformers()
			);
		}

		if ( $this->system_instruction ) {
			$params['systemInstruction'] = $this->system_instruction->to_array();
		}

		if ( $this->safety_settings ) {
			$params['safetySettings'] = array_map(
				static function ( Safety_Setting $safety_setting ) {
					return $safety_setting->to_array();
				},
				$this->safety_settings
			);
		}

		return array_filter( $params );
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $response_data The response data.
	 * @param ?Candidates          $prev_chunk_candidates The candidates from the previous chunk in case of a streaming
	 *                                                    response, or null.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response_data, ?Candidates $prev_chunk_candidates = null ): Candidates {
		if ( ! isset( $response_data['candidates'] ) ) {
			throw $this->api->create_missing_response_key_exception( 'candidates' );
		}

		$this->check_non_empty_candidates( $response_data['candidates'] );

		if ( null === $prev_chunk_candidates ) {
			$other_data = $response_data;
			unset( $other_data['candidates'] );

			$candidates = new Candidates();
			foreach ( $response_data['candidates'] as $index => $candidate_data ) {
				$other_candidate_data = $candidate_data;
				unset( $other_candidate_data['content'] );

				$candidates->add_candidate(
					new Candidate(
						$this->prepare_candidate_content( $candidate_data, $index ),
						array_merge( $other_candidate_data, $other_data )
					)
				);
			}

			return $candidates;
		}

		// Subsequent chunk of a streaming response.
		$candidates_data = $this->merge_candidates_chunk(
			$prev_chunk_candidates->to_array(),
			$response_data
		);

		return Candidates::from_array( $candidates_data );
	}

	/**
	 * Merges a streaming response chunk with the previous candidates data.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed> $candidates_data The candidates data from the previous chunk.
	 * @param array<string, mixed> $chunk_data      The response chunk data.
	 * @return array<string, mixed> The merged candidates data.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function merge_candidates_chunk( array $candidates_data, array $chunk_data ): array {
		if ( ! isset( $chunk_data['candidates'] ) ) {
			throw $this->api->create_missing_response_key_exception( 'candidates' );
		}

		$other_data = $chunk_data;
		unset( $other_data['candidates'] );

		foreach ( $chunk_data['candidates'] as $index => $candidate_data ) {
			$candidates_data[ $index ] = array_merge( $candidates_data[ $index ], $candidate_data, $other_data );
		}

		return $candidates_data;
	}

	/**
	 * Transforms a given candidate from the API response into a Content instance.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed> $candidate_data The API response candidate data.
	 * @param int                  $index          The index of the candidate in the response.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_candidate_content( array $candidate_data, int $index ): Content {
		if ( ! isset( $candidate_data['content']['parts'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->api->create_missing_response_key_exception( "candidates.{$index}.content.parts" );
		}

		$role = isset( $candidate_data['content']['role'] ) && 'user' === $candidate_data['content']['role']
			? Content_Role::USER
			: Content_Role::MODEL;

		return new Content(
			$role,
			Parts::from_array( $candidate_data['content']['parts'] )
		);
	}

	/**
	 * Checks that the response includes candidates with content.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed>[] $candidates_data The candidates data from the response.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not include any candidates with content.
	 */
	private function check_non_empty_candidates( array $candidates_data ): void {
		$errors = array();
		foreach ( $candidates_data as $candidate_data ) {
			if ( ! isset( $candidate_data['content'] ) ) {
				if ( isset( $candidate_data['finishReason'] ) ) {
					$errors[] = $candidate_data['finishReason'];
				} else {
					$errors[] = 'unknown';
				}
			}
		}

		if ( count( $errors ) === count( $candidates_data ) ) {
			$message = __( 'The response does not include any candidates with content.', 'ai-services' );

			$errors = array_unique(
				array_filter(
					$errors,
					static function ( $error ) {
						return 'unknown' !== $error;
					}
				)
			);
			if ( count( $errors ) > 0 ) {
				$message .= ' ' . sprintf(
					/* translators: %s: finish reason code */
					__( 'Finish reason: %s', 'ai-services' ),
					implode(
						wp_get_list_item_separator(),
						$errors
					)
				);
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->api->create_response_exception( $message );
		}
	}

	/**
	 * Gets the content transformers.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, callable> The content transformers.
	 *
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	private static function get_content_transformers(): array {
		return array(
			'role'  => static function ( Content $content ) {
				return $content->get_role();
			},
			'parts' => static function ( Content $content ) {
				$parts = array();
				foreach ( $content->get_parts() as $part ) {
					if ( $part instanceof Text_Part ) {
						$parts[] = array( 'text' => $part->get_text() );
					} elseif ( $part instanceof Inline_Data_Part ) {
						$mime_type = $part->get_mime_type();
						if (
							str_starts_with( $mime_type, 'image/' )
							|| str_starts_with( $mime_type, 'audio/' )
						) {
							$parts[] = array(
								'inlineData' => array(
									'mimeType' => $mime_type,
									// The Google AI API expects inlineData blobs to be without the prefix.
									'data'     => preg_replace(
										'/^data:[a-z0-9-]+\/[a-z0-9-]+;base64,/',
										'',
										$part->get_base64_data()
									),
								),
							);
						} else {
							throw new Generative_AI_Exception(
								esc_html__( 'The Google AI API only supports text, image, audio, function call, and function response parts.', 'ai-services' )
							);
						}
					} elseif ( $part instanceof File_Data_Part ) {
						$mime_type = $part->get_mime_type();
						if (
							str_starts_with( $mime_type, 'image/' )
							|| str_starts_with( $mime_type, 'audio/' )
						) {
							$parts[] = array(
								'fileData' => array(
									'mimeType' => $mime_type,
									'fileUri'  => $part->get_file_uri(),
								),
							);
						} else {
							throw new Generative_AI_Exception(
								esc_html__( 'The Google AI API only supports text, image, audio, function call, and function response parts.', 'ai-services' )
							);
						}
					} elseif ( $part instanceof Function_Call_Part ) {
						$parts[] = array(
							'functionCall' => array(
								'name' => $part->get_name(),
								'args' => $part->get_args(),
							),
						);
					} elseif ( $part instanceof Function_Response_Part ) {
						$parts[] = array(
							'functionResponse' => array(
								'name'     => $part->get_name(),
								'response' => $part->get_response(),
							),
						);
					} else {
						throw new Generative_AI_Exception(
							esc_html__( 'The Google AI API only supports text, image, audio, function call, and function response parts.', 'ai-services' )
						);
					}
				}
				return $parts;
			},
		);
	}

	/**
	 * Gets the generation configuration transformers.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	private static function get_generation_config_transformers(): array {
		return array(
			'stopSequences'    => static function ( Text_Generation_Config $config ) {
				return $config->get_stop_sequences();
			},
			'responseMimeType' => static function ( Text_Generation_Config $config ) {
				return $config->get_response_mime_type();
			},
			'responseSchema'   => static function ( Text_Generation_Config $config ) {
				if ( $config->get_response_mime_type() === 'application/json' ) {
					return $config->get_response_schema();
				}
				return array();
			},
			'responseModalities' => static function ( Text_Generation_Config $config ) {
				return array();
			},
			'candidateCount'   => static function ( Text_Generation_Config $config ) {
				return $config->get_candidate_count();
			},
			'maxOutputTokens'  => static function ( Text_Generation_Config $config ) {
				return $config->get_max_output_tokens();
			},
			'temperature'      => static function ( Text_Generation_Config $config ) {
				// In the Google AI API temperature ranges from 0.0 to 2.0.
				return $config->get_temperature() * 2.0;
			},
			'topP'             => static function ( Text_Generation_Config $config ) {
				return $config->get_top_p();
			},
			'topK'             => static function ( Text_Generation_Config $config ) {
				return $config->get_top_k();
			},
			'presencePenalty'  => static function ( Text_Generation_Config $config ) {
				return $config->get_presence_penalty();
			},
			'frequencyPenalty' => static function ( Text_Generation_Config $config ) {
				return $config->get_frequency_penalty();
			},
			'responseLogprobs' => static function ( Text_Generation_Config $config ) {
				return $config->get_response_logprobs();
			},
			'logprobs'         => static function ( Text_Generation_Config $config ) {
				return $config->get_logprobs();
			},
		);
	}

	/**
	 * Prepares the API request tools parameter for the model.
	 *
	 * @since 0.5.0
	 *
	 * @param Tools $tools The tools to prepare the parameter with.
	 * @return array<string, mixed>[] The tools parameter value.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid tool is provided.
	 */
	private function prepare_tools_param( Tools $tools ): array {
		$tools_param = array();

		foreach ( $tools as $tool ) {
			if ( ! $tool instanceof Function_Declarations_Tool ) {
				throw new InvalidArgumentException(
					esc_html__( 'Invalid tool: Only function declarations tools are supported.', 'ai-services' )
				);
			}

			$function_declarations = $tool->get_function_declarations();
			$declarations_data     = array();
			foreach ( $function_declarations as $declaration ) {
				$declarations_data[] = array_filter(
					array(
						'name'        => $declaration['name'],
						'description' => $declaration['description'] ?? null,
						'parameters'  => isset( $declaration['parameters'] ) ? $this->remove_additional_properties_key( $declaration['parameters'] ) : null,
					)
				);
			}

			$tools_param[] = array(
				'functionDeclarations' => $declarations_data,
			);
		}

		return $tools_param;
	}

	/**
	 * Removes the `additionalProperties` key from the schema, including child schemas.
	 *
	 * This is necessary because the Google AI API will reject the schema if it contains this key.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $schema The schema to remove the `additionalProperties` key from.
	 * @return array<string, mixed> The schema without the `additionalProperties` key.
	 */
	private function remove_additional_properties_key( array $schema ): array {
		if ( isset( $schema['additionalProperties'] ) ) {
			unset( $schema['additionalProperties'] );
		}
		if ( isset( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $key => $child_schema ) {
				$schema['properties'][ $key ] = $this->remove_additional_properties_key( $child_schema );
			}
		}
		return $schema;
	}

	/**
	 * Prepares the API request tool config parameter for the model.
	 *
	 * @since 0.5.0
	 *
	 * @param Tool_Config $tool_config The tool config to prepare the parameter with.
	 * @return array<string, mixed> The tool config parameter value.
	 */
	private function prepare_tool_config_param( Tool_Config $tool_config ): array {
		$tool_config_param = array(
			'functionCallingConfig' => array(
				// Either 'auto' or 'any'.
				'mode' => strtoupper( $tool_config->get_function_call_mode() ),
			),
		);

		if ( 'ANY' === $tool_config_param['functionCallingConfig']['mode'] ) {
			$allowed_function_names = $tool_config->get_allowed_function_names();
			if ( count( $allowed_function_names ) > 0 ) {
				$tool_config_param['functionCallingConfig']['allowedFunctionNames'] = $allowed_function_names;
			}
		}

		return $tool_config_param;
	}
}
