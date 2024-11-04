<?php
/**
 * Class Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Anthropic;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use InvalidArgumentException;

/**
 * Class representing an Anthropic AI model.
 *
 * @since 0.1.0
 */
class Anthropic_AI_Model implements Generative_AI_Model, With_Multimodal_Input, With_Text_Generation {
	use With_Text_Generation_Trait;

	/**
	 * The Anthropic AI API instance.
	 *
	 * @since 0.1.0
	 * @var Anthropic_AI_API_Client
	 */
	private $api;

	/**
	 * The model slug.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $model;

	/**
	 * The generation configuration.
	 *
	 * @since 0.1.0
	 * @var Generation_Config|null
	 */
	private $generation_config;

	/**
	 * The system instruction.
	 *
	 * @since 0.1.0
	 * @var Content|null
	 */
	private $system_instruction;

	/**
	 * The request options.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	private $request_options;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Anthropic_AI_API_Client $api             The Anthropic AI API instance.
	 * @param string                  $model           The model slug.
	 * @param array<string, mixed>    $model_params    Optional. Additional model parameters. See
	 *                                                 {@see Anthropic_AI_Service::get_model()} for the list of
	 *                                                 available parameters. Default empty array.
	 * @param array<string, mixed>    $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Anthropic_AI_API_Client $api, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->api             = $api;
		$this->request_options = $request_options;

		$this->model = $model;

		if ( isset( $model_params['generationConfig'] ) ) {
			if ( $model_params['generationConfig'] instanceof Generation_Config ) {
				$this->generation_config = $model_params['generationConfig'];
			} else {
				$this->generation_config = Generation_Config::from_array( $model_params['generationConfig'] );
			}
		}

		if ( isset( $model_params['systemInstruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['systemInstruction'] );
		}
	}

	/**
	 * Gets the model slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The model slug.
	 */
	public function get_model_slug(): string {
		return $this->model;
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
		$transformers = self::get_content_transformers();

		$params = array(
			// TODO: Add support for tools and tool config, to support code generation.
			'messages' => array_map(
				static function ( Content $content ) use ( $transformers ) {
					return Transformer::transform_content( $content, $transformers );
				},
				$contents
			),
		);
		if ( $this->system_instruction ) {
			$params['system'] = Helpers::content_to_text( $this->system_instruction );
		}
		if ( $this->generation_config ) {
			$params = Transformer::transform_generation_config_params(
				array_merge( $this->generation_config->get_additional_args(), $params ),
				$this->generation_config,
				self::get_generation_config_transformers()
			);
		}

		$request  = $this->api->create_generate_content_request(
			$this->model,
			array_filter( $params ),
			array_merge(
				$this->request_options,
				$request_options
			)
		);
		$response = $this->api->make_request( $request );

		$candidates = new Candidates();
		$candidates->add_candidate(
			new Candidate(
				$this->prepare_api_response_for_content( $response ),
				$response
			)
		);

		return $candidates;
	}

	/**
	 * Transforms a given API response into a Content instance.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $response The API response.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_api_response_for_content( array $response ): Content {
		if ( ! isset( $response['content'] ) || ! $response['content'] ) {
			throw new Generative_AI_Exception(
				esc_html(
					sprintf(
						/* translators: %s: key name */
						__( 'The response from the Anthropic API is missing the "%s" key.', 'ai-services' ),
						'content'
					)
				)
			);
		}

		$role = isset( $response['role'] ) && 'user' === $response['role']
			? Content_Role::USER
			: Content_Role::MODEL;

		$parts = array();
		foreach ( $response['content'] as $part ) {
			// TODO: Support decoding tool call responses.
			if ( 'text' === $part['type'] ) {
				$parts[] = array( 'text' => $part['text'] );
			} else {
				throw new Generative_AI_Exception(
					esc_html__( 'The response from the Anthropic API includes an unexpected content part.', 'ai-services' )
				);
			}
		}

		return Content::from_array(
			array(
				'role'  => $role,
				'parts' => $parts,
			)
		);
	}

	/**
	 * Gets the content transformers.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The content transformers.
	 */
	private static function get_content_transformers(): array {
		return array(
			'role'    => static function ( Content $content ) {
				if ( $content->get_role() === Content_Role::MODEL ) {
					return 'assistant';
				}
				return 'user';
			},
			'content' => static function ( Content $content ) {
				$parts = array();
				foreach ( $content->get_parts() as $part ) {
					if ( $part instanceof Text_Part ) {
						$parts[] = array(
							'type' => 'text',
							'text' => $part->get_text(),
						);
					} elseif ( $part instanceof Inline_Data_Part ) {
						$mime_type = $part->get_mime_type();
						if ( ! str_starts_with( $mime_type, 'image/' ) ) {
							throw new InvalidArgumentException(
								esc_html__( 'Invalid content part: The Anthropic API only supports text and inline image parts.', 'ai-services' )
							);
						}
						$parts[] = array(
							'type'   => 'image',
							'source' => array(
								'type'       => 'base64',
								'media_type' => $mime_type,
								'data'       => $part->get_base64_data(),
							),
						);
					} else {
						throw new InvalidArgumentException(
							esc_html__( 'Invalid content part: The Anthropic API only supports text and inline image parts.', 'ai-services' )
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
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	private static function get_generation_config_transformers(): array {
		return array(
			'stop_sequences' => static function ( Generation_Config $config ) {
				return $config->get_stop_sequences();
			},
			'max_tokens'     => static function ( Generation_Config $config ) {
				$max_tokens = $config->get_max_output_tokens();
				if ( ! $max_tokens ) {
					// The 'max_tokens' parameter is required in the Anthropic API, so we need a default.
					return 4096;
				}
				return $max_tokens;
			},
			'temperature'    => static function ( Generation_Config $config ) {
				$temperature = $config->get_temperature();
				if ( $temperature > 1.0 ) {
					// The Anthropic API only supports a temperature of up to 1.0, so we need to cap it.
					return 1.0;
				}
				return $temperature;
			},
			'top_p'          => static function ( Generation_Config $config ) {
				return $config->get_top_p();
			},
			'top_k'          => static function ( Generation_Config $config ) {
				return $config->get_top_k();
			},
		);
	}
}
